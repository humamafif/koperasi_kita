<?php
// filepath: d:\Development\menpro\koperasi_kita\app\Filament\Widgets\SHUStatsWidget.php

namespace App\Filament\Widgets;

use App\Models\SHUDistribution;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class SHUStatsWidget extends BaseWidget
{
    protected static ?string $pollingInterval = '60s';
    protected static ?int $sort = 4;

    protected function getStats(): array
    {
        $totalSHU = SHUDistribution::sum('jumlah_shu');

        $totalDistributed = SHUDistribution::where('status', 'dibagikan')->sum('jumlah_shu');

        $latestYear = SHUDistribution::max('tahun');

        $totalPending = SHUDistribution::where('status', 'pending')->sum('jumlah_shu');

        $totalAnggota = SHUDistribution::select('user_id')
            ->where('status', 'dibagikan')
            ->distinct()
            ->count();

        $averageSHU = $totalAnggota > 0
            ? $totalDistributed / $totalAnggota
            : 0;

        return [
            Stat::make('Total SHU Diproses', 'Rp ' . number_format($totalSHU, 0, ',', '.'))
                ->description('Total SHU yang dihitung')
                ->descriptionIcon('heroicon-m-calculator')
                ->color('primary'),

            Stat::make('Total SHU Didistribusikan', 'Rp ' . number_format($totalDistributed, 0, ',', '.'))
                ->description('Sudah dibagikan kepada anggota')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),

            Stat::make('SHU Menunggu Distribusi', 'Rp ' . number_format($totalPending, 0, ',', '.'))
                ->description('Belum didistribusikan')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

            Stat::make('Rata-rata SHU Anggota', 'Rp ' . number_format($averageSHU, 0, ',', '.'))
                ->description('Rata-rata SHU per anggota')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('info'),
        ];
    }
}
