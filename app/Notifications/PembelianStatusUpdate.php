<?php

namespace App\Notifications;

use App\Models\PembelianProduk;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Filament\Notifications\Notification as FilamentNotification;

class PembelianStatusUpdate extends Notification implements ShouldQueue
{
    use Queueable;

    protected $pembelian;

    public function __construct(PembelianProduk $pembelian)
    {
        $this->pembelian = $pembelian;
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'title' => 'Status Pembelian Diperbarui',
            'message' => "Status pembelian {$this->pembelian->produk->nama} diperbarui menjadi {$this->pembelian->status}",
            'pembelian_id' => $this->pembelian->id,
        ];
    }

    public function toFilament($notifiable): FilamentNotification
    {
        $statusColors = [
            'pending' => 'warning',
            'diproses' => 'primary',
            'dikirim' => 'info',
            'selesai' => 'success',
            'dibatalkan' => 'danger',
        ];

        return FilamentNotification::make()
            ->title('Status Pembelian Diperbarui')
            ->icon('heroicon-o-shopping-cart')
            ->body("Status pembelian {$this->pembelian->produk->nama} diperbarui menjadi {$this->pembelian->status}")
            ->color($statusColors[$this->pembelian->status] ?? 'primary')
            ->actions([
                \Filament\Notifications\Actions\Action::make('view')
                    ->button()
                    ->url(route('filament.anggota.resources.pembelian-saya.view', ['record' => $this->pembelian->id])),
            ]);
    }
}
