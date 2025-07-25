<?php

namespace App\Providers\Filament;

use App\Filament\Widgets\SHUStatsWidget;
use App\Filament\Widgets\SimpananTrendsWidget;
use Filament\Panel;
use Filament\PanelProvider;
use App\Http\Middleware\EnsureUserIsAdmin;
use Filament\Http\Middleware\Authenticate;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Filament\Http\Middleware\AuthenticateSession;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->spa()
            ->id('admin')
            ->path('admin')
            ->registration()
            ->globalSearch(false)
            ->login()
            ->colors([
                'primary' => "#6BB914",
            ])
            ->font('Aileron')
            ->homeUrl('/')
            ->navigationGroups([
                'Simpanan & Pinjaman',
                'Keuangan',
                'Keanggotaan',
                'Manajemen Produk'
            ])
            ->brandLogo(asset('assets/logo.png'))
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Dashboard::class,
                \App\Filament\Pages\BrowseProduk::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                \App\Filament\Widgets\PendapatanBungaWidget::class,
                \App\Filament\Widgets\KoperasiFinanceDetailWidget::class,
                \App\Filament\Widgets\ProdukStatsWidget::class,
                \App\Filament\Widgets\PendapatanBungaChart::class,
                SimpananTrendsWidget::class,
                SHUStatsWidget::class
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
                EnsureUserIsAdmin::class,
            ]);
    }
}
