<?php

namespace App\Filament\Widgets;

use App\Models\Pinjaman;
use App\Models\TagihanAnggota;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PendapatanBungaWidget extends BaseWidget
{
    protected static ?int $sort = 5;
    protected function getStats(): array
    {
        $totalBungaAkumulasi = Pinjaman::where('status', 'disetujui')
            ->sum('total_bunga');
        $totalPinjaman = Pinjaman::where('status', 'disetujui')
            ->sum('jumlah');
        $totalBungaTerbayar = $this->hitungTotalBungaTerbayar();
        $bungaBelumTerbayar = $totalBungaAkumulasi - $totalBungaTerbayar;
        return [
            Stat::make('Total Bunga Pinjaman', 'Rp ' . number_format($totalBungaAkumulasi, 0, ',', '.'))
                ->description('Total bunga seluruh pinjaman')
                ->color('success'),
            Stat::make('Total Pinjaman', 'Rp ' . number_format($totalPinjaman, 0, ',', '.'))
                ->description('Total seluruh pinjaman')
                ->color('success'),

            // Stat::make('Bunga Terbayar', 'Rp ' . number_format($totalBungaTerbayar, 0, ',', '.'))
            //     ->description('Bunga yang sudah dibayar')
            //     ->color('primary'),

            // Stat::make('Bunga Tertunda', 'Rp ' . number_format($bungaBelumTerbayar, 0, ',', '.'))
            //     ->description('Bunga yang belum dibayar')
            //     ->color('warning'),
        ];
    }

    private function hitungTotalBungaTerbayar()
    {
        $tagihanLunas = TagihanAnggota::where('jenis_tagihan', 'angsuran_pinjaman')
            ->where('tagihan_anggotas.status', 'lunas')
            ->whereNotNull('pinjaman_id')
            ->get();
        $totalBungaTerbayar = 0;

        foreach ($tagihanLunas as $tagihan) {
            $pinjaman = $tagihan->pinjaman;
            if ($pinjaman) {
                $totalBunga = $pinjaman->total_bunga ?? 0;
                $tenor = $pinjaman->tenorPinjaman->durasi ?? 0;
                if ($tenor > 0) {
                    $bungaPerAngsuran = $totalBunga / $tenor;
                    $totalBungaTerbayar += $bungaPerAngsuran;
                }
            }
        }
        return $totalBungaTerbayar;
    }
}
