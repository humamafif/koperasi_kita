<?php

namespace App\Filament\Resources\PinjamanResource\Pages;

use App\Filament\Resources\PinjamanResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditPinjaman extends EditRecord
{
    protected static string $resource = PinjamanResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if ($data['status'] === 'disetujui' || $data['status'] === 'ditolak') {
            $data['tanggal_persetujuan'] = now();
            $data['disetujui_oleh'] = Auth::user()->name;
        }

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
