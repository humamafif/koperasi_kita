<?php

namespace App\Filament\Anggota\Widgets;

use App\Models\Pinjaman;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class PinjamanStatsWidget extends BaseWidget
{
    public static function canView(): bool
    {
        return Auth::user()->hasRole('anggota_tetap');
    }
    protected function getStats(): array
    {
        $userId = Auth::id();

        $totalPinjaman = Pinjaman::where('user_id', $userId)
            ->where('status', 'disetujui')
            ->sum('jumlah');

        $pinjamanPending = Pinjaman::where('user_id', $userId)
            ->where('status', 'pending')
            ->count();

        $pinjamanAktif = Pinjaman::where('user_id', $userId)
            ->where('status', 'disetujui')
            ->count();

        $rataBunga = Pinjaman::where('user_id', $userId)
            ->where('status', 'disetujui')
            ->whereHas('tenorPinjaman')
            ->get()
            ->avg(function ($pinjaman) {
                return $pinjaman->nilai_bunga;
            });

        return [
            Stat::make('Total Pinjaman', 'Rp ' . number_format($totalPinjaman, 0, ',', '.'))
                ->description('Total pinjaman yang disetujui')
                ->color('success'),
            Stat::make('Pinjaman Aktif', $pinjamanAktif)
                ->description('Pinjaman yang sedang berjalan')
                ->color($pinjamanAktif > 0 ? 'info' : 'gray'),

            Stat::make('Pengajuan Pending', $pinjamanPending)
                ->description('Menunggu persetujuan')
                ->color($pinjamanPending > 0 ? 'warning' : 'gray'),


            // Stat::make('Rata-rata Bunga', $rataBunga ? number_format($rataBunga, 2) . '%' : '-')
            //     ->description('Rata-rata bunga pinjaman aktif')
            //     ->color('primary'),
        ];
    }
}
