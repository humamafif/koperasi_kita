<?php

namespace App\Filament\Anggota\Resources\PinjamanResource\Pages;

use App\Filament\Anggota\Resources\PinjamanResource;
use App\Models\BungaPinjaman;
use App\Models\TenorPinjaman;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreatePinjaman extends CreateRecord
{
    protected static string $resource = PinjamanResource::class;
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = Auth::id();
        $data['status'] = 'pending';
        $data['tanggal_pengajuan'] = now();

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
