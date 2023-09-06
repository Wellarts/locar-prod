<?php

namespace App\Filament\Pages;

use App\Models\CustoVeiculo;
use App\Models\Locacao;
use App\Models\Veiculo;
use Closure;
use Filament\Forms\Components\Card;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Forms;
use Filament\Tables;

class LucroVeiculo extends Page implements HasForms, HasTable 
{

    use InteractsWithForms, InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.lucro-veiculo';

    protected static ?string $title = 'Lucratividade por Veículo';

    protected static ?string $navigationGroup = 'Consultas';

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

    

    public function mount(): void
    {
        $this->form->fill();
    }


    protected function getFormSchema(): array 
    {
        return [
            Card::make()
                ->schema([
                    Forms\Components\Select::make('veiculo_id')
                        ->options(Veiculo::pluck('placa', 'id'))
                        ->reactive()
                        ->searchable()
                        ->label('Veículo')
                        ->afterStateUpdated(function ($state, callable $set, Closure $get,) {
                              //  dd($state);

                              $total_locacao = Locacao::where('veiculo_id', $state)->sum('valor_total_desconto');
                              $total_custo = CustoVeiculo::where('veiculo_id', $state)->sum('valor');
                            
                                $set('total_locacao', $total_locacao);
                                $set('total_custo', $total_custo );
                                $set('lucro', $total_locacao - $total_custo);
                           

                        }),
                    Forms\Components\TextInput::make('total_locacao')
                        ->disabled()                       
                        ->label('Total de Locação R$:'),   
                    Forms\Components\TextInput::make('total_custo')
                        ->disabled()   
                        ->label('Total de Custos R$:'),   
                    Forms\Components\TextInput::make('lucro')
                        ->disabled()   
                        ->label('Lucro Real R$:'),   
           
           
       
                ])->columns(2)->inlineLabel()
        ];

                        
     } 

}    
