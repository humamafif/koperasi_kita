<?php

namespace App\Filament\Anggota\Resources\PembelianSayaResource\Pages;

use App\Filament\Anggota\Resources\PembelianSayaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPembelianSaya extends EditRecord
{
    protected static string $resource = PembelianSayaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
