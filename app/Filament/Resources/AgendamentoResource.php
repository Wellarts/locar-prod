<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AgendamentoResource\Pages;
use App\Filament\Resources\AgendamentoResource\RelationManagers;
use App\Models\Agendamento;
use App\Models\Cliente;
use App\Models\Veiculo;
use Carbon\Carbon;
use Closure;
use Filament\Forms;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Grid;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use GuzzleHttp\Client;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Nette\Utils\Callback;
use NunoMaduro\Collision\Adapters\Phpunit\State;
use Stevebauman\Purify\Facades\Purify;

class AgendamentoResource extends Resource
{
    protected static ?string $model = Agendamento::class;

    protected static ?string $navigationGroup = 'Locar';

    protected static ?string $navigationIcon = 'heroicon-s-currency-dollar';


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Card::make()
                    ->schema([
                        Fieldset::make('Dados do Agendamento')
                            ->schema([
                            Forms\Components\Select::make('cliente_id')
                                ->label('Cliente')
                                ->required()
                                ->options(Cliente::all()->pluck('nome', 'id')->toArray()),
                            Forms\Components\Select::make('veiculo_id')
                                ->label('Veículo')
                                ->required()
                                ->allowHtml()
                                ->searchable()
                                ->getSearchResultsUsing(function (string $search) {
                                    $veiculos = Veiculo::where('modelo', 'like', "%{$search}%")->limit(50)->get();

                                    return $veiculos->mapWithKeys(function ($veiculos) {
                                          return [$veiculos->getKey() => static::getCleanOptionString($veiculos)];
                                         })->toArray();
                                })
                               ->getOptionLabelUsing(function ($value): string {
                                   $veiculo = Veiculo::find($value);

                                   return static::getCleanOptionString($veiculo);
                               }),
                              //  ->options(Veiculo::all()->pluck('modelo', 'id')->toArray()),
                            Forms\Components\DatePicker::make('data_saida')
                                ->displayFormat('d/m/Y')
                                ->label('Data Saída')
                                ->reactive()
                                ->required(),
                            Forms\Components\TimePicker::make('hora_saida')
                                ->label('Hora Saída')
                                ->required(),
                            Forms\Components\DatePicker::make('data_retorno')
                                ->displayFormat('d/m/Y')
                                ->label('Data Retorno')
                                ->reactive()
                                ->afterStateUpdated(function ($state, callable $set, Closure $get) {
                                    $dt_saida = Carbon::parse($get('data_saida'));
                                    $dt_retorno = Carbon::parse($get('data_retorno'));
                                    $qtd_dias = $dt_retorno->diffInDays($dt_saida);
                                    $set('qtd_diarias', $qtd_dias);

                                    $carro = Veiculo::find($get('veiculo_id'));
                                    $set('valor_total', ($carro->valor_diaria * $qtd_dias));

                                })
                                ->required(),
                            Forms\Components\TimePicker::make('hora_retorno')
                                ->label('Hora Retorno')
                                ->required(),
                            ]),
                        Fieldset::make('Valores')
                            ->schema([
                                Forms\Components\TextInput::make('qtd_diarias')
                                    ->disabled()
                                    ->label('Qtd Diárias')
                                    ->required(),
                                Forms\Components\TextInput::make('valor_total')
                                    ->disabled()
                                    ->label('Valor Total')
                                    ->required(),
                                Forms\Components\TextInput::make('valor_desconto')
                                    ->label('Valor Desconto'),
                                Forms\Components\TextInput::make('valor_pago')
                                    ->required()
                                    ->label('Valor Pago')
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $set, Closure $get,) {
                                        $set('valor_restante', (((float)$get('valor_total') - (float)$get('valor_desconto')) - (float)$get('valor_pago')));

                                    }),

                                Forms\Components\TextInput::make('valor_restante')
                                    ->disabled()
                                    ->label('Valor Restante')
                                    ->required(),
                                Forms\Components\Textarea::make('obs')
                                    ->label('Observações'),
                                Forms\Components\Toggle::make('status')
                                    ->label('Finalizar Agendamentos'),

                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('cliente.nome')
                    ->sortable()
                    ->searchable()
                    ->label('Cliente'),
                Tables\Columns\TextColumn::make('veiculo.modelo')
                    ->sortable()
                    ->searchable()
                    ->label('Veículo'),
                Tables\Columns\TextColumn::make('veiculo.placa')
                    ->searchable()
                     ->label('Placa'),
                Tables\Columns\TextColumn::make('data_saida')
                    ->label('Data Saída')
                    ->date(),
                Tables\Columns\TextColumn::make('hora_saida')
                    ->sortable()
                    ->label('Hora Saída'),
                Tables\Columns\TextColumn::make('data_retorno')
                    ->label('Data Retorno')
                    ->date(),
                Tables\Columns\TextColumn::make('hora_retorno')
                    ->label('Hora Retorno'),
                Tables\Columns\TextColumn::make('qtd_diarias')
                    ->label('Qtd Diárias'),
                Tables\Columns\TextColumn::make('valor_total')
                    ->label('Valor Total')
                    ->money('BRL'),
                Tables\Columns\TextColumn::make('valor_desconto')
                    ->label('Valor Desconto')
                    ->money('BRL'),
                Tables\Columns\TextColumn::make('valor_pago')
                    ->label('Valor Pago')
                    ->money('BRL'),
                Tables\Columns\TextColumn::make('valor_restante')
                    ->label('Valor Restante')
                    ->money('BRL'),
                Tables\Columns\TextColumn::make('obs')
                    ->label('Observações'),
                Tables\Columns\ToggleColumn::make('status')
                    ->label('Finalizado'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime(),
            ])
            ->filters([
                Filter::make('agendados')
                ->query(fn (Builder $query): Builder => $query->where('status', false)),
                 SelectFilter::make('cliente')->relationship('cliente', 'nome'),
                 SelectFilter::make('veiculo')->relationship('veiculo', 'placa'),
                 Tables\Filters\Filter::make('datas')
                    ->form([
                        Forms\Components\DatePicker::make('data_saida_de')
                            ->label('Saída de:'),
                        Forms\Components\DatePicker::make('data_retorno_ate')
                            ->label('Retorno até:'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['data_saida_de'],
                                fn($query) => $query->whereDate('data_saida', '>=', $data['data_saida_de']))
                            ->when($data['data_retorno_ate'],
                                fn($query) => $query->whereDate('data_retorno', '<=', $data['data_retorno_ate']));
                    })
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->modalHeading('Editar Agendamento'),
                    Tables\Actions\Action::make('Imprimir')
                        ->url(fn (Agendamento $record): string => route('imprimirAgendamento', $record))
                        ->openUrlInNewTab(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAgendamentos::route('/'),
            'create' => Pages\CreateAgendamento::route('/create'),
            'edit' => Pages\EditAgendamento::route('/{record}/edit'),
        ];
    }

    public static function getCleanOptionString(Veiculo $veiculo): string
    {
        return Purify::clean(
                view('filament.componentes.modelo-placa')
                    ->with('modelo', $veiculo?->modelo)
                    ->with('placa', $veiculo?->placa)
                    ->render()
        );
    }
}
