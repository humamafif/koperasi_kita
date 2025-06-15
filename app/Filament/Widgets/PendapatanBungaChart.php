<?php

namespace App\Filament\Widgets;

use App\Models\Pinjaman;
use App\Models\TagihanAnggota;
use Filament\Widgets\ChartWidget;
use Carbon\Carbon;

class PendapatanBungaChart extends ChartWidget
{
    protected static ?string $heading = 'Pendapatan Bunga Pinjaman (6 Bulan Terakhir)';

    protected int|string|array $columnSpan = '1/2';
    protected static ?int $sort = 6;

    protected function getData(): array
    {
        $months = collect();
        $pendapatan = collect();

        // Ambil 6 bulan terakhir
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $months->push($date->format('M Y'));

            // Dapatkan tagihan yang lunas untuk bulan tersebut
            $tagihanLunas = TagihanAnggota::where('jenis_tagihan', 'angsuran_pinjaman')
                ->where('status', 'lunas')
                ->whereMonth('tanggal_pembayaran', $date->month)
                ->whereYear('tanggal_pembayaran', $date->year)
                ->whereNotNull('pinjaman_id')
                ->get();

            $bungaBulanIni = 0;

            foreach ($tagihanLunas as $tagihan) {
                $pinjaman = Pinjaman::find($tagihan->pinjaman_id);

                if ($pinjaman && $pinjaman->total_bunga > 0) {
                    $totalBunga = $pinjaman->total_bunga;
                    $tenor = $pinjaman->tenorPinjaman ? $pinjaman->tenorPinjaman->durasi : 0;

                    if ($tenor > 0) {
                        $bungaPerAngsuran = $totalBunga / $tenor;
                        $bungaBulanIni += $bungaPerAngsuran;
                    }
                }
            }

            $pendapatan->push($bungaBulanIni);
        }

        return [
            'datasets' => [
                [
                    'label' => 'Pendapatan Bunga',
                    'data' => $pendapatan->toArray(),
                    'backgroundColor' => '#10b981', // Emerald 500
                    'borderColor' => '#059669', // Emerald 600
                ],
            ],
            'labels' => $months->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
