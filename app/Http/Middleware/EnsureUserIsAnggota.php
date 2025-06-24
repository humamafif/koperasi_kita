<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAnggota
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::user()->hasRole('anggota')) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Tidak memiliki akses'], 403);
            }
            return redirect()->route('filament.admin.auth.login');
        }
        return $next($request);
    }
}
