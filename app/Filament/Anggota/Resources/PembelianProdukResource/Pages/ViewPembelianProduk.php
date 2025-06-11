<?php

namespace App\Filament\Anggota\Resources\PembelianProdukResource\Pages;

use App\Filament\Anggota\Resources\PembelianProdukResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Auth;

class ViewPembelianProduk extends ViewRecord
{
    protected static string $resource = PembelianProdukResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->visible(
                    fn() =>
                    $this->record->penjual_id === Auth::id() &&
                        !in_array($this->record->status, ['selesai', 'dibatalkan'])
                ),

            Actions\Action::make('updateStatus')
                ->label('Update Status')
                ->icon('heroicon-o-arrow-path')
                ->form([
                    \Filament\Forms\Components\Select::make('status')
                        ->label('Status Baru')
                        ->options([
                            'pending' => 'Menunggu',
                            'diproses' => 'Diproses',
                            'dikirim' => 'Dikirim',
                            'selesai' => 'Selesai',
                            'dibatalkan' => 'Dibatalkan',
                        ])
                        ->required(),
                ])
                ->action(function (array $data): void {
                    $oldStatus = $this->record->status;
                    $this->record->status = $data['status'];
                    $this->record->save();

                    // Jika dibatalkan, kembalikan stok
                    if ($data['status'] === 'dibatalkan' && $oldStatus !== 'dibatalkan') {
                        $this->record->produk->stok += $this->record->jumlah;
                        $this->record->produk->save();
                    }

                    $this->notify('success', 'Status pembelian berhasil diperbarui.');
                })
                ->visible(
                    fn() =>
                    $this->record->penjual_id === Auth::id() &&
                        !in_array($this->record->status, ['selesai', 'dibatalkan'])
                ),
        ];
    }
}
