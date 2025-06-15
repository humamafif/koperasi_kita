<?php

namespace App\Filament\Anggota\Resources\SHUResource\Pages;

use App\Filament\Anggota\Resources\SHUResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSHUS extends ListRecords
{
    protected static string $resource = SHUResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
