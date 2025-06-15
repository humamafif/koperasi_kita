<?php

namespace App\Filament\Anggota\Widgets;

use App\Models\Simpanan;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class SimpananStatsWidget extends BaseWidget
{
    protected static ?int $sort = 4;
    public static function canView(): bool
    {
        return Auth::user()->hasRole('anggota_tetap');
    }
    protected function getStats(): array
    {
        $userId = Auth::id();

        $totalSimpananPokok = Simpanan::where('user_id', $userId)
            ->where('jenis', 'pokok')
            ->where('status', 'disetujui')
            ->sum('jumlah');

        $totalSimpananWajib = Simpanan::where('user_id', $userId)
            ->where('jenis', 'wajib')
            ->where('status', 'disetujui')
            ->sum('jumlah');

        $totalSimpananSukarela = Simpanan::where('user_id', $userId)
            ->where('jenis', 'sukarela')
            ->where('status', 'disetujui')
            ->sum('jumlah');

        $totalSimpananPending = Simpanan::where('user_id', $userId)
            ->where('status', 'pending')
            ->count();

        return [
            Stat::make('Simpanan Pokok', 'Rp ' . number_format($totalSimpananPokok, 0, ',', '.'))
                ->description('Total simpanan pokok')
                ->color('primary'),

            Stat::make('Simpanan Wajib', 'Rp ' . number_format($totalSimpananWajib, 0, ',', '.'))
                ->description('Total simpanan wajib')
                ->color('success'),

            Stat::make('Simpanan Sukarela', 'Rp ' . number_format($totalSimpananSukarela, 0, ',', '.'))
                ->description('Total simpanan sukarela')
                ->color('info'),

            Stat::make('Simpanan Pending', $totalSimpananPending)
                ->description('Menunggu verifikasi')
                ->color($totalSimpananPending > 0 ? 'warning' : 'gray'),
        ];
    }
}
