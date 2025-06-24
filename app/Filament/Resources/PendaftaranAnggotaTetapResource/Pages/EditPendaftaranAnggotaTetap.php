<?php

namespace App\Filament\Resources\PendaftaranAnggotaTetapResource\Pages;

use App\Filament\Resources\PendaftaranAnggotaTetapResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPendaftaranAnggotaTetap extends EditRecord
{
    protected static string $resource = PendaftaranAnggotaTetapResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
