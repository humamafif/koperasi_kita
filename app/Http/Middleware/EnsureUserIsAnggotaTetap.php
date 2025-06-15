<?php

// filepath: d:\Development\menpro\koperasi_kita\app\Http\Middleware\EnsureUserIsAnggotaTetap.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAnggotaTetap
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check() || !Auth::user()->hasRole('anggota_tetap')) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Akses terbatas hanya untuk anggota tetap'], 403);
            }
            return redirect()->route('filament.anggota.pages.daftar-anggota-tetap')
                ->with('error', 'Fitur ini hanya tersedia untuk anggota tetap. Silahkan upgrade keanggotaan Anda.');
        }

        return $next($request);
    }
}
