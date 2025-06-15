<?php

namespace App\Filament\Anggota\Resources\ProdukResource\Pages;

use App\Filament\Anggota\Resources\ProdukResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateProduk extends CreateRecord
{
    protected static string $resource = ProdukResource::class;
    protected static ?string $title = 'Tambah Produk';
    protected static bool $canCreateAnother = false;
    protected function getFormActions(): array
    {
        return [
            Actions\Action::make('create')
                ->label('Simpan Produk')
                ->icon('heroicon-o-plus')
                ->submit('create'),

            Actions\Action::make('cancel')
                ->outlined()
                ->url(ProdukResource::getUrl('index'))
        ];
    }
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = Auth::id();

        return $data;
    }
}
