<?php

namespace App\Filament\Anggota\Widgets;

use App\Models\SHUDistribution;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class SHUStatsWidget extends BaseWidget
{
    protected static ?int $sort = 2;
    public static function canView(): bool
    {
        return Auth::user()->hasRole('anggota_tetap');
    }

    protected function getStats(): array
    {
        $userId = Auth::id();

        // Total SHU yang diterima
        $totalSHU = SHUDistribution::where('user_id', $userId)
            ->where('status', 'dibagikan')
            ->sum('jumlah_shu');

        // SHU tahun terakhir
        $tahunTerakhir = SHUDistribution::where('user_id', $userId)
            ->where('status', 'dibagikan')
            ->latest('tahun')
            ->first();

        $jumlahSHUTerakhir = $tahunTerakhir ? $tahunTerakhir->jumlah_shu : 0;
        $tahunSHUTerakhir = $tahunTerakhir ? $tahunTerakhir->tahun : 'N/A';

        // Rata-rata persentase kontribusi
        $rataKontribusi = SHUDistribution::where('user_id', $userId)
            ->where('status', 'dibagikan')
            ->avg('persentase_kontribusi');

        $rataKontribusiFormatted = $rataKontribusi ? number_format($rataKontribusi * 100, 2) : 0;

        return [
            Stat::make('Total SHU Diterima', 'Rp ' . number_format($totalSHU, 0, ',', '.'))
                ->description('Total akumulasi SHU')
                ->color('success'),

            Stat::make('SHU Tahun ' . $tahunSHUTerakhir, 'Rp ' . number_format($jumlahSHUTerakhir, 0, ',', '.'))
                ->description('SHU terbaru yang diterima')
                ->color('primary'),

            Stat::make('Rata-rata Kontribusi', $rataKontribusiFormatted . '%')
                ->description('Persentase kontribusi rata-rata')
                ->color('info'),
        ];
    }
}
