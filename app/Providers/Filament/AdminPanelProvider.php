<?php

namespace App\Providers\Filament;

use App\Filament\Widgets\ContasPagarHoje;
use App\Filament\Widgets\ContasReceberHoje;
use App\Filament\Widgets\LocacaoMes;
use App\Filament\Widgets\SomatorioLocacao;
use App\Filament\Widgets\StatsVeiculos;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationItem;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Blade;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->databaseNotifications()
            ->favicon(asset('img/logo1.png'))
            ->brandLogo(asset('img/logo1.png'))
            ->brandLogoHeight('4rem')
            ->colors([
                'primary' => Color::Amber,
            ])
            ->resources([
                config('filament-logger.activity_resource')
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
             //   Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
                // Widgets\FilamentInfoWidget::class,
                // SomatorioLocacao::class,
                // LocacaoMes::class,
                // ContasReceberHoje::class,
                // ContasPagarHoje::class,
                // StatsVeiculos::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                   function (): string {
                      return Blade::render('@laravelPWA');
                   }
              )

            ->navigationItems([
                NavigationItem::make('Manuais')
                    ->url('https://drive.google.com/drive/folders/1Pt9pkPfRKporD7Q3oLafggKGpHu4Xw46?usp=sharing', shouldOpenInNewTab: true)
                    ->icon('heroicon-s-question-mark-circle')
                    ->group('Ajuda')
                    ->sort(3),
            ]);
    }
}
