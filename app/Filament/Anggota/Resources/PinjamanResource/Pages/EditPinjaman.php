<?php

namespace App\Filament\Anggota\Resources\PinjamanResource\Pages;

use App\Filament\Anggota\Resources\PinjamanResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPinjaman extends EditRecord
{
    protected static string $resource = PinjamanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
