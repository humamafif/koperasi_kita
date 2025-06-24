<?php

namespace App\Filament\Resources\TagihanAnggotaResource\Pages;

use App\Filament\Resources\TagihanAnggotaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTagihanAnggotas extends ListRecords
{
    protected static string $resource = TagihanAnggotaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
