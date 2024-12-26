<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClienteResource\Pages;
use App\Filament\Resources\ClienteResource\RelationManagers;
use App\Models\Cliente;
use App\Models\Estado;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;


class ClienteResource extends Resource
{
    protected static ?string $model = Cliente::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'Cadastros';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make([
                    'xl' => 3,
                    '2xl' => 3,
                ])
                    ->schema([
                Forms\Components\TextInput::make('nome')
                    ->columnSpan([
                        'xl' => 2,
                        '2xl' => 2,
                    ])
                    ->maxLength(255),
                Forms\Components\TextInput::make('cpf_cnpj')
                    ->label('CPF/CNPJ')
                    ->mask(RawJs::make(<<<'JS'
                                    $input.length > 14 ? '99.999.999/9999-99' : '999.999.999-99'
                                JS))
                            ->rule('cpf_ou_cnpj'),
                Forms\Components\Textarea::make('endereco')
                    ->label('Endereço')
                    ->columnSpanFull(),
                    Forms\Components\Select::make('estado_id')
                    ->label('Estado')
                    ->native(false)
                    ->searchable()
                    ->required()
                    ->options(Estado::all()->pluck('nome', 'id')->toArray())
                    ->reactive(),
                Forms\Components\Select::make('cidade_id')
                    ->label('Cidade')
                    ->native(false)
                    ->searchable()
                    ->required()
                    ->options(function (callable $get) {
                        $estado = Estado::find($get('estado_id'));
                        if (!$estado) {
                            return Estado::all()->pluck('nome', 'id');
                        }
                        return $estado->cidade->pluck('nome', 'id');
                    })
                    ->reactive(),
                Forms\Components\TextInput::make('telefone_1')
                    ->label('Telefone 1')
                    ->tel()
                    ->mask('(99)99999-9999'),
                Forms\Components\TextInput::make('telefone_2')
                    ->tel()
                    ->label('Telefone 2')
                    ->tel()
                    ->mask('(99)99999-9999'),
                Forms\Components\TextInput::make('email')
                    ->columnSpan([
                        'xl' => 2,
                        '2xl' => 2,
                    ])
                    ->email()
                    ->maxLength(255),
                Forms\Components\TextInput::make('rede_social')
                    ->label('Rede Social'),
                Forms\Components\TextInput::make('cnh')
                        ->label('CNH'),
                Forms\Components\DatePicker::make('validade_cnh')
                     //   ->format('d/m/Y')
                        ->label('Valiade da CNH'),
                Forms\Components\TextInput::make('rg')
                        ->label('RG'),
                Forms\Components\TextInput::make('exp_rg')
                        ->label('Orgão Exp.'),
                Forms\Components\Select::make('estado_exp_rg')
                        ->searchable()
                        ->label('UF - Expedidor')
                        ->options(Estado::all()->pluck('nome', 'id')->toArray()),
                FileUpload::make('img_cnh')
                            ->columnSpan([
                                'xl' => 2,
                                '2xl' => 2,
                            ])
                        ->downloadable()
                        ->label('Foto CNH'),

                Forms\Components\DatePicker::make('data_nascimento')
                            ->label('Data de Nascimento'),
                ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
            Tables\Columns\TextColumn::make('nome')
                ->sortable()
                ->searchable(),
            Tables\Columns\TextColumn::make('endereco')
                ->label('Endereço'),
            Tables\Columns\TextColumn::make('estado.nome')
                ->label('Estado'),
            Tables\Columns\TextColumn::make('cidade.nome')
                ->label('Cidade'),
            Tables\Columns\TextColumn::make('telefone_1')

              //  ->formatStateUsing(fn (string $state) => vsprintf('(%d%d)%d%d%d%d%d-%d%d%d%d', str_split($state)))
                ->label('Telefone'),

            Tables\Columns\TextColumn::make('email')
                ->label('Email'),
            Tables\Columns\TextColumn::make('created_at')
                ->toggleable(isToggledHiddenByDefault: true)
                ->dateTime(),
            Tables\Columns\TextColumn::make('updated_at')
                ->dateTime()
                ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageClientes::route('/'),
        ];
    }
}
