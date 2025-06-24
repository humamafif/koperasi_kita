<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckAnggotaTetapStatus
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return $next($request);
        }

        // Cek jika user sudah login dan mengakses halaman simpanan
        if ($request->is('anggota/simpanan*') && !Auth::user()->is_anggota_tetap) {
            // Jika ingin selalu redirect, uncomment baris berikut:
            // return redirect()->route('filament.anggota.pages.daftar-anggota-tetap');

            // Set session flag untuk menampilkan modal
            session()->flash('show_anggota_tetap_modal', true);
        }

        return $next($request);
    }
}
