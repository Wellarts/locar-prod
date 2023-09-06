<?php

namespace App\Filament\Pages;

use App\Filament\Resources\LocacaoResource\Widgets\StateLocacao;
use App\Filament\Widgets\AgendamentosMes;
use App\Filament\Widgets\LocacoesMes;
use App\Models\Temp_lucratividade;
use App\Models\Veiculo;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Widgets\AccountWidget;

class Dashbord extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.dashbord';

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

        $dados = new LocacaoPorMes();
        $dados->mount();
               
      $veiculos = Veiculo::all();

        foreach($veiculos as $veiculo){
            if($veiculo->km_atual >= $veiculo->aviso_troca_oleo){
                Notification::make()
                ->title('ATENÇÃO: Veículos com troca de óleo próxima')
                ->body('Veiculo: '.$veiculo->modelo.' Placa: '.$veiculo->placa) 
                ->danger()
                ->persistent() 
                ->send();
            }

            if($veiculo->km_atual >= $veiculo->aviso_troca_filtro){
                Notification::make()
                ->title('ATENÇÃO: Veículos com troca do filtro próxima')
                ->body('Veiculo: '.$veiculo->modelo.' Placa: '.$veiculo->placa) 
                ->danger()
                ->persistent() 
                ->send();
            }

            if($veiculo->km_atual >= $veiculo->aviso_troca_correia){
                Notification::make()
                ->title('ATENÇÃO: Veículos com troca da correia próxima')
                ->body('Veiculo: '.$veiculo->modelo.' Placa: '.$veiculo->placa) 
                ->danger()
                ->persistent() 
                ->send();
            }

            if($veiculo->km_atual >= $veiculo->aviso_troca_pastilha){
                Notification::make()
                ->title('ATENÇÃO: Veículos com troca da pastilha próxima')
                ->body('Veiculo: '.$veiculo->modelo.' Placa: '.$veiculo->placa) 
                ->danger()
                ->persistent() 
                ->send();
            }
        }
    } 

    protected function getHeaderWidgets(): array
    {

        

        return [
            AccountWidget::class,
            StateLocacao::class,
            LocacoesMes::class,
            AgendamentosMes::class,
            
        ];
    }


}
