<?php

namespace App\Filament\Anggota\Resources\SimpananResource\Pages;

use App\Filament\Anggota\Resources\SimpananResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateSimpanan extends CreateRecord
{
    protected static string $resource = SimpananResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = Auth::id();
        $data['status'] = 'pending';
        $data['tanggal_pembayaran'] = now();

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
