<?php

namespace App\Providers\Filament;


use App\Http\Middleware\CheckAnggotaTetapStatus;
use App\Http\Middleware\EnsureUserIsAnggota;
use App\Models\PendaftaranAnggotaTetap;
use Filament\Enums\ThemeMode;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AnggotaPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('anggota')
            ->spa()
            ->defaultThemeMode(ThemeMode::Light)
            ->path('anggota')
            ->brandName('Koperasi MerahPutih')
            ->login()
            ->homeUrl('/')
            ->registration()
            ->font('Aileron')
            ->colors([
                'primary' => '#E31E24',
            ])
            ->globalSearch(false)
            ->brandLogo(asset('assets/logo.png'))
            ->discoverResources(in: app_path('Filament/Anggota/Resources'), for: 'App\\Filament\\Anggota\\Resources')
            ->discoverPages(in: app_path('Filament/Anggota/Pages'), for: 'App\\Filament\\Anggota\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Anggota/Widgets'), for: 'App\\Filament\\Anggota\\Widgets')
            ->widgets([
                // Widgets\AccountWidget::class,
                \App\Filament\Anggota\Widgets\SaldoWidget::class,
                \App\Filament\Anggota\Widgets\PinjamanStatsWidget::class,
                \App\Filament\Anggota\Widgets\SimpananStatsWidget::class,
                \App\Filament\Anggota\Widgets\ProdukStatsWidget::class,
                \App\Filament\Anggota\Widgets\TagihanStatsWidget::class,
                \App\Filament\Anggota\Widgets\SHUStatsWidget::class,
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
                CheckAnggotaTetapStatus::class,
            ])
            ->authMiddleware([
                Authenticate::class,
                EnsureUserIsAnggota::class,
            ])
            ->renderHook(
                'panels::content.start',
                function () {
                    if (!Auth::check()) {
                        return '';
                    }

                    $user = Auth::user();
                    if ($user->hasRole('anggota_tetap')) {
                        return '';
                    }
                    $pendingRegistration = PendaftaranAnggotaTetap::where('user_id', $user->id)
                        ->where('status', 'pending')
                        ->exists();

                    if ($pendingRegistration) {
                        return view('components.pending-registration-banner')->render();
                    }
                    return view('components.upgrade-banner')->render();
                }
            )
            ->renderHook(
                'panels::body.end',
                fn() => view('components.anggota-tetap-modal')->render(),
                'panels::auth.login.form.after',
            );
    }
}
