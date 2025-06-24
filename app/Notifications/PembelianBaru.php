<?php
namespace App\Notifications;

use App\Models\PembelianProduk;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Filament\Notifications\Notification as FilamentNotification;

class PembelianBaru extends Notification implements ShouldQueue
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
            'title' => 'Pesanan Baru',
            'message' => "Produk {$this->pembelian->produk->nama} dibeli oleh {$this->pembelian->pembeli->name}",
            'pembelian_id' => $this->pembelian->id,
        ];
    }

    public function toFilament($notifiable): FilamentNotification
    {
        return FilamentNotification::make()
            ->title('Pesanan Baru')
            ->icon('heroicon-o-shopping-cart')
            ->body("Produk {$this->pembelian->produk->nama} dibeli oleh {$this->pembelian->pembeli->name}")
            ->actions([
                \Filament\Notifications\Actions\Action::make('view')
                    ->button()
                    ->url(route('filament.anggota.resources.pembelian-produks.view', ['record' => $this->pembelian->id])),
            ]);
    }
}
