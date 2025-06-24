<?php

namespace App\Filament\Resources\PendaftaranAnggotaTetapResource\Pages;

use App\Filament\Resources\PendaftaranAnggotaTetapResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPendaftaranAnggotaTetaps extends ListRecords
{
    protected static string $resource = PendaftaranAnggotaTetapResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
