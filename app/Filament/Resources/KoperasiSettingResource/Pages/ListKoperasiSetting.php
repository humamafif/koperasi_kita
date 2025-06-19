<?php
// filepath: d:\Development\menpro\koperasi_kita\app\Filament\Resources\KoperasiSettingResource\Pages\ListKoperasiSettings.php

namespace App\Filament\Resources\KoperasiSettingResource\Pages;

use App\Filament\Resources\KoperasiSettingResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Cache;

class ListKoperasiSetting extends ListRecords
{
    protected static string $resource = KoperasiSettingResource::class;
    protected static ?string $title = 'Pengaturan Koperasi';

    // No create button needed
    protected function getHeaderActions(): array
    {
        return [];
    }
}
