<?php

namespace App\Filament\Anggota\Resources\SimpananResource\Pages;

use App\Filament\Anggota\Resources\SimpananResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;

class ListSimpanans extends ListRecords
{
    protected static string $resource = SimpananResource::class;

    protected function getHeaderActions(): array
    {
        // Tampilkan tombol Create hanya jika user adalah anggota tetap
        if (Auth::user()->is_anggota_tetap) {
            return [
                Actions\CreateAction::make(),
            ];
        }

        return [];
    }

    protected function getViewData(): array
    {
        $data = parent::getViewData();
        $data['isAnggotaTetap'] = Auth::user()->is_anggota_tetap;

        return $data;
    }
}
