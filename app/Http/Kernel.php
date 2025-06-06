<?php

namespace App\Http;

use App\Http\Middleware\CheckAnggotaTetapStatus;
use App\Http\Middleware\EnsureUserIsAdmin;
use App\Http\Middleware\EnsureUserIsAnggotaTetap;
use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    // Middleware global
    protected $middleware = [];

    // Grup middleware
    protected $middlewareGroups = [
        'web' => [
            // Middleware web yang sudah ada...
        ],

        'api' => [
            // Middleware API yang sudah ada...
        ],

        // Tambahkan grup middleware untuk panel Filament Anggota
        'filament.anggota' => [
            // Middleware yang sudah ada...
            CheckAnggotaTetapStatus::class,
        ],
    ];

    // Middleware alias
    protected $middlewareAliases = [
        // Alias yang sudah ada...
        'anggota.tetap.check' => CheckAnggotaTetapStatus::class,
        'admin.check' => EnsureUserIsAdmin::class,
        'anggota.tetap' => EnsureUserIsAnggotaTetap::class,
    ];
}
