<?php
// filepath: d:\Development\menpro\koperasi_kita\app\Observers\PembelianProdukObserver.php

namespace App\Observers;

use App\Models\PembelianProduk;
use Filament\Notifications\Notification;

class PembelianProdukObserver
{
    public function created(PembelianProduk $pembelianProduk)
    {
        // Kirim notifikasi ke penjual
        $penjual = $pembelianProduk->penjual;

        Notification::make()
            ->title('Pesanan Baru')
            ->body("Produk {$pembelianProduk->produk->nama} dibeli oleh {$pembelianProduk->pembeli->name}")
            ->actions([
                \Filament\Notifications\Actions\Action::make('view')
                    ->button()
                    ->url('/anggota/pembelian-produks')
            ])
            ->sendToDatabase($penjual);
    }

    public function updated(PembelianProduk $pembelianProduk)
    {
        // Jika status diupdate, kirim notifikasi
        if ($pembelianProduk->isDirty('status')) {
            $oldStatus = $pembelianProduk->getOriginal('status');
            $newStatus = $pembelianProduk->status;

            // Kirim notifikasi ke pembeli tentang perubahan status
            if ($oldStatus !== $newStatus) {
                $pembeli = $pembelianProduk->pembeli;

                Notification::make()
                    ->title('Status Pembelian Diperbarui')
                    ->body("Status pembelian {$pembelianProduk->produk->nama} diperbarui menjadi {$pembelianProduk->status}")
                    ->actions([
                        \Filament\Notifications\Actions\Action::make('view')
                            ->button()
                            ->url('/anggota/pembelian-saya'),
                    ])
                    ->sendToDatabase($pembeli);
            }
        }
        if ($pembelianProduk->isDirty('status_pembayaran') && $pembelianProduk->status_pembayaran === 'terverifikasi') {
            if ($pembelianProduk->status === 'pending') {
                $pembelianProduk->status = 'diproses';
                $pembelianProduk->save();
            }
        }
    }
}
