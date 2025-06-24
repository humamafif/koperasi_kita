<?php
// filepath: d:\Development\menpro\koperasi_kita\app\Filament\Anggota\Widgets\TagihanStatsWidget.php

namespace App\Filament\Anggota\Widgets;

use App\Models\TagihanAnggota;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class TagihanStatsWidget extends BaseWidget
{
    protected static ?int $sort = 6;
    public static function canView(): bool
    {
        return Auth::user()->hasRole('anggota_tetap');
    }

    protected function getStats(): array
    {
        $userId = Auth::id();

        $tagihanBelumBayar = TagihanAnggota::where('user_id', $userId)
            ->where('status', 'belum_bayar')
            ->count();

        $jumlahTagihanBelumBayar = TagihanAnggota::where('user_id', $userId)
            ->where('status', 'belum_bayar')
            ->sum('jumlah');

        $tagihanMenungguVerifikasi = TagihanAnggota::where('user_id', $userId)
            ->where('status', 'menunggu_verifikasi')
            ->count();

        $tagihanLunas = TagihanAnggota::where('user_id', $userId)
            ->where('status', 'lunas')
            ->count();
        $tagihanJatuhTempo = TagihanAnggota::where('user_id', $userId)
            ->where('status', 'belum_bayar')
            ->where('tanggal_jatuh_tempo', '<=', now()->addDays(7))
            ->count();

        $tagihanTerlambat = TagihanAnggota::where('user_id', $userId)
            ->where('status', 'belum_bayar')
            ->where('tanggal_jatuh_tempo', '<', now())
            ->count();

        return [
            Stat::make('Tagihan Belum Dibayar', $tagihanBelumBayar)
                ->description('Tagihan yang perlu dibayar')
                ->descriptionIcon('heroicon-m-exclamation-circle')
                ->color($tagihanBelumBayar > 0 ? 'danger' : 'success'),

            Stat::make('Total Tagihan', 'Rp ' . number_format($jumlahTagihanBelumBayar, 0, ',', '.'))
                ->description('Jumlah tagihan yang belum dibayar')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color($jumlahTagihanBelumBayar > 0 ? 'warning' : 'success'),

            Stat::make('Menunggu Verifikasi', $tagihanMenungguVerifikasi)
                ->description('Pembayaran dalam proses verifikasi')
                ->descriptionIcon('heroicon-m-clock')
                ->color($tagihanMenungguVerifikasi > 0 ? 'warning' : 'gray'),

            Stat::make('Tagihan Lunas', $tagihanLunas)
                ->description('Tagihan yang sudah dibayar')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('Jatuh Tempo 7 Hari', $tagihanJatuhTempo)
                ->description('Tagihan akan segera jatuh tempo')
                ->descriptionIcon('heroicon-m-clock')
                ->color($tagihanJatuhTempo > 0 ? 'warning' : 'gray'),

            Stat::make('Tagihan Terlambat', $tagihanTerlambat)
                ->description('Tagihan melewati jatuh tempo')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($tagihanTerlambat > 0 ? 'danger' : 'gray'),
        ];
    }
}
