<?php

namespace App\Filament\Anggota\Widgets;

use App\Models\PembelianProduk;
use App\Models\Produk;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProdukStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $userId = Auth::id();
        $totalProduk = Produk::where('user_id', $userId)->count();
        $totalProdukAktif = Produk::where('user_id', $userId)->where('aktif', true)->count();
        $totalPembelian = PembelianProduk::where('penjual_id', $userId)->count();
        $pendapatanTotal = PembelianProduk::where('penjual_id', $userId)
            ->where('status', 'selesai')
            ->sum('total') ?? 0;

        return [
            Stat::make('Total Produk', $totalProduk)
                ->description('Jumlah produk yang Anda jual')
                ->descriptionIcon('heroicon-m-shopping-bag')
                ->color('primary'),

            Stat::make('Produk Aktif', $totalProdukAktif)
                ->description('Produk tersedia di katalog')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('Total Pembelian', $totalPembelian)
                ->description('Jumlah pembelian produk Anda')
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->color('info'),

            Stat::make('Pendapatan', 'Rp ' . number_format($pendapatanTotal, 0, ',', '.'))
                ->description('Total pendapatan dari produk Anda')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),
        ];
    }

    public static function canView(): bool
    {
        return Auth::user()->hasRole('anggota_tetap');
    }
}
