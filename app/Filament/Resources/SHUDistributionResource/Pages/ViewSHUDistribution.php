<?php
// filepath: d:\Development\menpro\koperasi_kita\app\Filament\Resources\SHUDistributionResource\Pages\ViewSHUDistribution.php

namespace App\Filament\Resources\SHUDistributionResource\Pages;

use App\Filament\Resources\SHUDistributionResource;
use App\Models\SHUBiaya;
use App\Services\SHUCalculationService;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class ViewSHUDistribution extends ViewRecord
{
    protected static string $resource = SHUDistributionResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Detail SHU Anggota')
                    ->schema([
                        Infolists\Components\TextEntry::make('user.name')
                            ->label('Anggota'),

                        Infolists\Components\TextEntry::make('tahun')
                            ->label('Tahun'),

                        Infolists\Components\TextEntry::make('total_simpanan')
                            ->label('Total Simpanan')
                            ->money('IDR'),

                        Infolists\Components\TextEntry::make('persentase_kontribusi')
                            ->label('Persentase Kontribusi')
                            ->formatStateUsing(fn($state) => number_format($state * 100, 2) . '%'),

                        Infolists\Components\TextEntry::make('jumlah_shu')
                            ->label('Jumlah SHU')
                            ->money('IDR'),

                        Infolists\Components\TextEntry::make('status')
                            ->label('Status')
                            ->badge()
                            ->color(fn(string $state): string => match ($state) {
                                'pending' => 'warning',
                                'dibagikan' => 'success',
                                'ditolak' => 'danger',
                                default => 'gray',
                            }),

                        Infolists\Components\TextEntry::make('tanggal_distribusi')
                            ->label('Tanggal Distribusi')
                            ->dateTime('d M Y H:i'),
                    ])
                    ->columns(2),

                Infolists\Components\Section::make('Komponen Biaya SHU Tahun ' . $this->record->tahun)
                    ->schema(function () {
                        $biaya = SHUBiaya::where('tahun', $this->record->tahun)->first();

                        if (!$biaya) {
                            return [
                                Infolists\Components\TextEntry::make('no_data')
                                    ->label('Data Komponen Biaya')
                                    ->state('Tidak ada data komponen biaya SHU untuk tahun ini')
                                    ->columnSpanFull(),
                            ];
                        }

                        return [
                            Infolists\Components\TextEntry::make('saldo_koperasi')
                                ->label('Saldo Koperasi')
                                ->money('IDR')
                                ->state($biaya->saldo_koperasi),

                            Infolists\Components\TextEntry::make('total_shu')
                                ->label('Total SHU Dibagikan')
                                ->money('IDR')
                                ->state($biaya->total_shu),

                            Infolists\Components\TextEntry::make('biaya_operasional')
                                ->label('Biaya Operasional')
                                ->money('IDR')
                                ->state($biaya->biaya_operasional),

                            Infolists\Components\TextEntry::make('pajak')
                                ->label('Pajak')
                                ->money('IDR')
                                ->state($biaya->pajak),

                            Infolists\Components\TextEntry::make('dana_cadangan')
                                ->label('Dana Cadangan')
                                ->money('IDR')
                                ->state($biaya->dana_cadangan),

                            Infolists\Components\TextEntry::make('biaya_lainnya')
                                ->label('Biaya Lainnya')
                                ->money('IDR')
                                ->state($biaya->biaya_lainnya),

                            Infolists\Components\TextEntry::make('total_biaya')
                                ->label('Total Biaya')
                                ->money('IDR')
                                ->state($biaya->total_biaya),

                            Infolists\Components\TextEntry::make('keterangan_biaya')
                                ->label('Keterangan Biaya Lainnya')
                                ->state($biaya->keterangan_biaya)
                                ->columnSpanFull()
                                ->visible(fn() => !empty($biaya->keterangan_biaya)),
                        ];
                    })
                    ->columns(2),
            ]);
    }
}
