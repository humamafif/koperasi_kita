<?php

namespace App\Filament\Anggota\Resources\TagihanSayaResource\Pages;

use App\Filament\Anggota\Resources\TagihanSayaResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditTagihanSaya extends EditRecord
{
    protected static string $resource = TagihanSayaResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['status'] = 'menunggu_verifikasi';
        $data['tanggal_pembayaran'] = now();

        return $data;
    }

    protected function beforeFill(): void
    {
        if ($this->record->status !== 'belum_bayar' || $this->record->user_id !== Auth::id()) {
            Notification::make()
                ->title('Tidak dapat mengakses tagihan')
                ->body('Anda hanya dapat membayar tagihan yang berstatus belum dibayar.')
                ->danger()
                ->send();

            $this->redirect(TagihanSayaResource::getUrl());
        }
    }

    protected function getRedirectUrl(): string
    {
        return TagihanSayaResource::getUrl();
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Pembayaran tagihan berhasil dikirim';
    }

    protected function getSavedNotificationMessage(): ?string
    {
        return 'Pembayaran Anda sedang menunggu verifikasi oleh admin.';
    }
}
