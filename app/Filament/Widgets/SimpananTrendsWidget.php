<?php


namespace App\Filament\Widgets;

use App\Models\Simpanan;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class SimpananTrendsWidget extends ChartWidget
{
    protected static ?string $heading = 'Trend Simpanan (6 Bulan Terakhir)';
    protected static ?int $sort = 7;

    protected function getData(): array
    {
        $months = collect(range(5, 0))->map(function ($i) {
            $date = Carbon::now()->subMonths($i)->startOfMonth();
            return [
                'month' => $date->format('M Y'),
                'timestamp' => $date->timestamp,
            ];
        });


        $simpananData = Simpanan::where('status', 'disetujui')
            ->where('tanggal_verifikasi', '>=', now()->subMonths(6)->startOfMonth())
            ->select(
                DB::raw('YEAR(tanggal_verifikasi) as year'),
                DB::raw('MONTH(tanggal_verifikasi) as month'),
                'jenis',
                DB::raw('SUM(jumlah) as total')
            )
            ->groupBy('year', 'month', 'jenis')
            ->get();


        $pokokData = $months->map(function ($month) use ($simpananData) {
            $start = Carbon::createFromTimestamp($month['timestamp']);
            $end = (clone $start)->endOfMonth();

            return $simpananData
                ->where('year', $start->year)
                ->where('month', $start->month)
                ->where('jenis', 'pokok')
                ->sum('total');
        })->toArray();

        $wajibData = $months->map(function ($month) use ($simpananData) {
            $start = Carbon::createFromTimestamp($month['timestamp']);
            $end = (clone $start)->endOfMonth();

            return $simpananData
                ->where('year', $start->year)
                ->where('month', $start->month)
                ->where('jenis', 'wajib')
                ->sum('total');
        })->toArray();

        $sukarelaData = $months->map(function ($month) use ($simpananData) {
            $start = Carbon::createFromTimestamp($month['timestamp']);
            $end = (clone $start)->endOfMonth();

            return $simpananData
                ->where('year', $start->year)
                ->where('month', $start->month)
                ->where('jenis', 'sukarela')
                ->sum('total');
        })->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Simpanan Pokok',
                    'data' => $pokokData,
                    'backgroundColor' => '#3b82f6',
                    'borderColor' => '#3b82f6',
                ],
                [
                    'label' => 'Simpanan Wajib',
                    'data' => $wajibData,
                    'backgroundColor' => '#10b981',
                    'borderColor' => '#10b981',
                ],
                [
                    'label' => 'Simpanan Sukarela',
                    'data' => $sukarelaData,
                    'backgroundColor' => '#6366f1',
                    'borderColor' => '#6366f1',
                ],
            ],
            'labels' => $months->pluck('month')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
