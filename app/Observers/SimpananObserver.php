<?php

namespace App\Observers;

use App\Models\RiwayatTransaksi;
use App\Models\SaldoKoperasi;
use App\Models\Simpanan;
use App\Notifications\SimpananStatusNotification;

class SimpananObserver
{
    /**
     * Handle the Simpanan "created" event.
     */
    public function created(Simpanan $simpanan): void
    {
        RiwayatTransaksi::create([
            'user_id' => $simpanan->user_id,
            'jenis_transaksi' => 'simpanan_' . $simpanan->jenis,
            'referensi_id' => $simpanan->id,
            'referensi_tipe' => 'App\\Models\\Simpanan',
            'jumlah' => $simpanan->jumlah,
            'tanggal' => $simpanan->tanggal_pembayaran,
            'status' => $simpanan->status,
            'keterangan' => $simpanan->keterangan ?? 'Simpanan ' . ucfirst($simpanan->jenis),
        ]);
    }

    /**
     * Handle the Simpanan "updated" event.
     */
    public function updated(Simpanan $simpanan): void
    {
        // Kirim notifikasi jika status simpanan berubah menjadi disetujui atau ditolak
        if ($simpanan->wasChanged('status')) {
            $newStatus = $simpanan->status;

            if ($newStatus === 'disetujui' || $newStatus === 'ditolak') {
                // Tidak perlu notifikasi untuk simpanan pokok saat pendaftaran anggota tetap
                // karena sudah ada notifikasi dari PendaftaranAnggotaTetapObserver
                if (!($simpanan->jenis === 'pokok' && $simpanan->user->pendaftaranAnggotaTetap)) {
                    $simpanan->user->notify(new SimpananStatusNotification($simpanan));
                }
            }
        }
        // Jika status berubah menjadi disetujui
        if ($simpanan->wasChanged('status') && $simpanan->status === 'disetujui') {
            // Tambah saldo koperasi
            SaldoKoperasi::tambah($simpanan->jumlah);

            // Kirim notifikasi jika diperlukan
            // Kode notifikasi yang sudah ada...
        }

        // Jika status berubah dari disetujui ke status lain
        if ($simpanan->wasChanged('status') && $simpanan->getOriginal('status') === 'disetujui' && $simpanan->status !== 'disetujui') {
            // Kurangi saldo koperasi
            SaldoKoperasi::kurang($simpanan->jumlah);
        }

        if ($simpanan->isDirty('status') || $simpanan->isDirty('jumlah')) {
            RiwayatTransaksi::where('referensi_id', $simpanan->id)
                ->where('referensi_tipe', 'App\\Models\\Simpanan')
                ->update([
                    'status' => $simpanan->status,
                    'jumlah' => $simpanan->jumlah,
                    'keterangan' => $simpanan->keterangan ?? 'Simpanan ' . ucfirst($simpanan->jenis),
                ]);
        }
    }

    /**
     * Handle the Simpanan "deleted" event.
     */
    public function deleted(Simpanan $simpanan): void
    {
        //
    }

    /**
     * Handle the Simpanan "restored" event.
     */
    public function restored(Simpanan $simpanan): void
    {
        //
    }

    /**
     * Handle the Simpanan "force deleted" event.
     */
    public function forceDeleted(Simpanan $simpanan): void
    {
        //
    }
}
