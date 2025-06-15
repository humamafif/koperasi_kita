<?php

namespace App\Filament\Resources\TenorPinjamanResource\Pages;

use App\Filament\Resources\TenorPinjamanResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateTenorPinjaman extends CreateRecord
{
    protected static ?string $title = 'Tambah Tenor Pinjaman';
    protected static string $resource = TenorPinjamanResource::class;
    protected static bool $canCreateAnother = false;
}
