<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Filament\Http\Responses\Auth\Contracts\LoginResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CustomLoginResponseController extends Controller implements LoginResponse
{
    public function toResponse($request): RedirectResponse
    {
        $user = Auth::user();
        Log::info('User roles:', $user->roles->pluck('name')->toArray());

        if ($user->hasRole('anggota')) {
            return redirect()->route('filament.anggota.pages.dashboard');
        }
        if ($user->hasRole(['admin', 'super_admin'])) {
            return redirect()->route('filament.admin.pages.dashboard');
        }
        return redirect()->intended(config('filament.anggota.home_url', 'anggota'));
    }
}
