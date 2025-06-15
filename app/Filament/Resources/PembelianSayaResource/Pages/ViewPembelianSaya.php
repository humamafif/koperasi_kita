<?php

namespace App\Filament\Resources\PembelianSayaResource\Pages;

use App\Filament\Resources\PembelianSayaResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewPembelianSaya extends ViewRecord
{
    protected static string $resource = PembelianSayaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('terima')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Konfirmasi Penerimaan')
                ->modalDescription('Dengan mengonfirmasi penerimaan, Anda menyatakan bahwa produk telah diterima dengan baik.')
                ->modalSubmitActionLabel('Ya, Saya Terima')
                ->action(function () {
                    $this->record->status = 'selesai';
                    $this->record->save();

                    $this->notify('success', 'Produk berhasil dikonfirmasi diterima.');
                })
                ->visible(fn() => $this->record->status === 'dikirim'),

            Actions\Action::make('batalkan')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Batalkan Pembelian')
                ->modalDescription('Apakah Anda yakin ingin membatalkan pembelian ini? Tindakan ini akan mengembalikan stok produk.')
                ->modalSubmitActionLabel('Ya, Batalkan')
                ->action(function () {
                    $this->record->status = 'dibatalkan';
                    $this->record->save();

                    // Kembalikan stok produk
                    $this->record->produk->stok += $this->record->jumlah;
                    $this->record->produk->save();

                    $this->notify('success', 'Pembelian berhasil dibatalkan.');
                })
                ->visible(fn() => $this->record->status === 'pending'),
        ];
    }
}
