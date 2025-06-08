<?php

namespace App\Filament\Anggota\Resources\PembelianProdukResource\Pages;

use App\Filament\Anggota\Resources\PembelianProdukResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPembelianProduk extends EditRecord
{
    protected static string $resource = PembelianProdukResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
