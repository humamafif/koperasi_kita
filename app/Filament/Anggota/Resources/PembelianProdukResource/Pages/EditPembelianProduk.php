<?php
// filepath: d:\Development\menpro\koperasi_kita\app\Filament\Anggota\Resources\PembelianProdukResource\Pages\EditPembelianProduk.php

namespace App\Filament\Anggota\Resources\PembelianProdukResource\Pages;

use App\Filament\Anggota\Resources\PembelianProdukResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditPembelianProduk extends EditRecord
{
    protected static string $resource = PembelianProdukResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function beforeSave(): void
    {
        // Jika status diubah menjadi dibatalkan, kembalikan stok
        $oldStatus = $this->record->status;
        $newStatus = $this->data['status'];

        if ($newStatus === 'dibatalkan' && $oldStatus !== 'dibatalkan') {
            $this->record->produk->stok += $this->record->jumlah;
            $this->record->produk->save();
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Jika bukan penjual atau status sudah selesai/dibatalkan, redirect ke view
        if ($this->record->penjual_id !== Auth::id() || in_array($this->record->status, ['selesai', 'dibatalkan'])) {
            $this->redirect($this->getResource()::getUrl('view', ['record' => $this->record]));
        }

        return $data;
    }
}
