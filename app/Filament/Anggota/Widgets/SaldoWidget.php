<?php

namespace App\Filament\Anggota\Widgets;

use App\Models\SaldoAnggota;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class SaldoWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    public static function canView(): bool
    {
        return Auth::user()->hasRole('anggota_tetap');
    }

    protected function getStats(): array
    {
        $userId = Auth::id();

        // Get saldo anggota
        $saldo = SaldoAnggota::firstOrCreate(
            ['user_id' => $userId],
            ['saldo' => 0]
        );

        // Get total SHU yang belum diambil
        $belumDiambil = \App\Models\SHUDistribution::where('user_id', $userId)
            ->where('status', 'dibagikan')
            ->where('is_claimed', false)
            ->sum('jumlah_shu');

        // Get total SHU yang sudah diambil
        $sudahDiambil = \App\Models\SHUDistribution::where('user_id', $userId)
            ->where('status', 'dibagikan')
            ->where('is_claimed', true)
            ->sum('jumlah_shu');

        return [
            Stat::make('Saldo Saya', 'Rp ' . number_format($saldo->saldo, 0, ',', '.'))
                ->description('Dana tersedia untuk penarikan')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),

            Stat::make('SHU Belum Diambil', 'Rp ' . number_format($belumDiambil, 0, ',', '.'))
                ->description('Silahkan klaim di menu SHU Saya')
                ->descriptionIcon('heroicon-m-arrow-path')
                ->color($belumDiambil > 0 ? 'warning' : 'gray')
                ->url('/anggota/shu'),

            Stat::make('Total SHU Sudah Diambil', 'Rp ' . number_format($sudahDiambil, 0, ',', '.'))
                ->description('Total SHU yang sudah diklaim')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('info'),
        ];
    }
}
