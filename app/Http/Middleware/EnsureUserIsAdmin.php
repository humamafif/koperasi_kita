<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check() || !(Auth::user()->hasRole('admin') || Auth::user()->hasRole('super_admin'))) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Tidak memiliki akses ke panel admin'], 403);
            }
            if (Auth::check() && Auth::user()->hasRole('anggota')) {
                return redirect()->route('filament.anggota.pages.dashboard')
                    ->with('error', 'Anda tidak memiliki akses ke panel admin.');
            }

            return redirect()->route('filament.admin.auth.login')
                ->with('error', 'Anda harus login sebagai admin untuk mengakses halaman ini.');
        }

        return $next($request);
    }
}
