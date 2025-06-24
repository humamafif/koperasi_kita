<?php

namespace App\Filament\Resources\RiwayatTransaksiResource\Pages;

use App\Filament\Resources\RiwayatTransaksiResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRiwayatTransaksi extends EditRecord
{
    protected static string $resource = RiwayatTransaksiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
