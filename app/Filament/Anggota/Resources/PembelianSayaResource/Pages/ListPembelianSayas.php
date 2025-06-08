<?php

namespace App\Filament\Anggota\Resources\PembelianSayaResource\Pages;

use App\Filament\Anggota\Resources\PembelianSayaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPembelianSayas extends ListRecords
{
    protected static string $resource = PembelianSayaResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
