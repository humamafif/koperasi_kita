<?php

namespace App\Filament\Anggota\Resources\SimpananResource\Pages;

use App\Filament\Anggota\Resources\SimpananResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateSimpanan extends CreateRecord
{
    protected static string $resource = SimpananResource::class;

    protected static ?string $title = 'Tambah Simpanan';

    protected function getFormActions(): array
    {
        return [
            Actions\Action::make('create')
                ->label('Simpan Simpanan')
                ->icon('heroicon-o-plus')
                ->submit('create'),

            Actions\Action::make('cancel')
                ->outlined()
                ->url(SimpananResource::getUrl('index'))
        ];
    }
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
