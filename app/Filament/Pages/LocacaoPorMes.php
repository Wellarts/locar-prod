<?php

namespace App\Filament\Pages;

use App\Models\Locacao;
use App\Models\Temp_lucratividade;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\View\View;

class LocacaoPorMes extends Page implements HasTable, HasForms
{

    use InteractsWithTable, InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.locacao-por-mes';

    protected static ?string $navigationGroup = 'Consultas';

    protected static ?string $title = 'Lucratividade Mensal';

    protected static function shouldRegisterNavigation(): bool
    {
        /** @var \App\Models\User */
        $authUser =  auth()->user();

        if($authUser->hasRole('Administrador') || $authUser->hasRole('demos'))
        {
              return true;
        }
        else
        {
            return false;
        }


    }


    public function mount()
    {
        Temp_lucratividade::truncate();

        $Locacoes = Locacao::all();
        
        foreach($Locacoes as $Locacao){
           
            $valorLocacaoDia = ($Locacao->valor_total_desconto / $Locacao->qtd_diarias);

           // dd($valorLocacaoDia);

                for($x=1;$x<=$Locacao->qtd_diarias;$x++){

                    $addLocacaoDia = [
                        'cliente_id'  => $Locacao->cliente_id,
                        'veiculo_id'  => $Locacao->veiculo_id,
                        'data_saida'  => $Locacao->data_saida,
                        'qtd_diaria'  => 1,
                        'valor_diaria'  => $valorLocacaoDia,
                    ];

                    Temp_lucratividade::create($addLocacaoDia);

                }
            
            

        }
    }

    protected function getTableQuery(): Builder|String
    {


        return Temp_lucratividade::query();

    }

    protected function getTableColumns(): array
    {

                return [
                            Tables\Columns\TextColumn::make('cliente.nome')
                                ->sortable()
                                ->searchable(),
                            Tables\Columns\TextColumn::make('veiculo.modelo')
                                ->sortable()
                                ->searchable()
                                ->label('Veículo'),
                            Tables\Columns\TextColumn::make('veiculo.placa')
                                ->searchable()
                                 ->label('Placa'),
                            Tables\Columns\TextColumn::make('data_saida')
                                ->label('Data Saída')
                                ->date('d/m/Y')
                                ->sortable()
                                ->alignCenter(),
                            Tables\Columns\TextColumn::make('qtd_diaria')
                                ->alignCenter()
                                ->label('Qtd Diárias'),
                            Tables\Columns\TextColumn::make('valor_diaria')
                                ->money('BRL')
                                ->label('Valor Total'),
                      

                        ];

    }

    protected function getTableFilters(): array
    {
       return [
        SelectFilter::make('cliente')->relationship('cliente', 'nome'),
         SelectFilter::make('veiculo')->relationship('veiculo', 'placa'),
         Tables\Filters\Filter::make('datas')
            ->form([
                DatePicker::make('data_saida_de')
                    ->label('Saída de:'),
                DatePicker::make('data_saida_ate')
                    ->label('Saída ate:'),
            ])
            ->query(function ($query, array $data) {
                return $query
                    ->when($data['data_saida_de'],
                        fn($query) => $query->whereDate('data_saida', '>=', $data['data_saida_de']))
                    ->when($data['data_saida_ate'],
                        fn($query) => $query->whereDate('data_saida', '<=', $data['data_saida_ate']));
           })

       ];


    }

    protected function getFooter(): View
    {

            return view('filament/footer/lucratividade-mes/footer');
    }





}
