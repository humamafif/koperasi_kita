<?php

namespace App\Filament\Resources\TagihanAnggotaResource\Pages;

use App\Filament\Resources\TagihanAnggotaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTagihanAnggota extends EditRecord
{
    protected static string $resource = TagihanAnggotaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
