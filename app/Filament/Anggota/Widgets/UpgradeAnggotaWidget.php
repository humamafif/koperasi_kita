<?php

namespace App\Filament\Anggota\Widgets;

use App\Models\PendaftaranAnggotaTetap;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

class UpgradeAnggotaWidget extends Widget
{
    protected static string $view = 'filament.anggota.widgets.upgrade-anggota-widget';

    public function getPendingRegistration()
    {
        return PendaftaranAnggotaTetap::where('user_id', Auth::id())
            ->where('status', 'pending')
            ->latest()
            ->first();
    }

    public static function canView(): bool
    {
        $user = Auth::user();
        if ($user->hasRole('anggota_tetap')) {
            return false;
        }

        return true;
    }
}
