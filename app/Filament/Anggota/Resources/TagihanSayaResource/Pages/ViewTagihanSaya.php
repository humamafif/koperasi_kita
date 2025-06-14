<?php

namespace App\Filament\Anggota\Resources\TagihanSayaResource\Pages;

use App\Filament\Anggota\Resources\TagihanSayaResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;

class ViewTagihanSaya extends ViewRecord
{
    protected static string $resource = TagihanSayaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('bayar')
                ->label('Bayar Tagihan')
                ->icon('heroicon-o-currency-dollar')
                ->color('success')
                ->url(fn() => TagihanSayaResource::getUrl('edit', ['record' => $this->record]))
                ->visible(fn() => $this->record->status === 'belum_bayar'),
        ];
    }

    protected function beforeFill(): void
    {
        // Jika tagihan bukan milik user yang login, redirect ke list
        if ($this->record->user_id !== Auth::id()) {
            Notification::make()
                ->title('Tidak dapat mengakses tagihan')
                ->body('Anda hanya dapat melihat tagihan milik Anda.')
                ->danger()
                ->send();

            $this->redirect(TagihanSayaResource::getUrl());
        }
    }
}
