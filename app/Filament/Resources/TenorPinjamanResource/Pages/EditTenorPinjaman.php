<?php

namespace App\Filament\Resources\TenorPinjamanResource\Pages;

use App\Filament\Resources\TenorPinjamanResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTenorPinjaman extends EditRecord
{
    protected static string $resource = TenorPinjamanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
