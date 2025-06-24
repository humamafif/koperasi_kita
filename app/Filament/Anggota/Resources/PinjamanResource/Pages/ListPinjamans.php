<?php

namespace App\Filament\Anggota\Resources\PinjamanResource\Pages;

use App\Filament\Anggota\Resources\PinjamanResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPinjamans extends ListRecords
{
    protected static string $resource = PinjamanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Ajukan Pinjaman Baru'),
        ];
    }
}
