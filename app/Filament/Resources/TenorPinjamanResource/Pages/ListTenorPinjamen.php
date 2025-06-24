<?php

namespace App\Filament\Resources\TenorPinjamanResource\Pages;

use App\Filament\Resources\TenorPinjamanResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTenorPinjamen extends ListRecords
{
    protected static string $resource = TenorPinjamanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
