<?php

namespace App\Providers;

use App\Models\PembelianProduk;
use App\Models\PendaftaranAnggotaTetap;
use App\Models\Pinjaman;
use App\Models\Simpanan;
use App\Models\TagihanAnggota;
use App\Observers\PembelianProdukObserver;
use App\Observers\PendaftaranAnggotaTetapObserver;
use App\Observers\PinjamanObserver;
use App\Observers\SimpananObserver;
use App\Observers\TagihanAnggotaObserver;
use Filament\Tables\Table;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    protected $listen = [
        Login::class => [
            \App\Listeners\LoginSuccessful::class,
        ]
    ];
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Pinjaman::observe(PinjamanObserver::class);
        Simpanan::observe(SimpananObserver::class);
        PembelianProduk::observe(PembelianProdukObserver::class);
        PendaftaranAnggotaTetap::observe(PendaftaranAnggotaTetapObserver::class);
        TagihanAnggota::observe(TagihanAnggotaObserver::class);
    }
}
