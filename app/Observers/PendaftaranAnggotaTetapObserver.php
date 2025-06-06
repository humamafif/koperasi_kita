<?php

namespace App\Observers;

use App\Models\PendaftaranAnggotaTetap;
use App\Notifications\PendaftaranAnggotaTetapNotification;

class PendaftaranAnggotaTetapObserver
{
    /**
     * Handle the PendaftaranAnggotaTetap "created" event.
     */
    public function created(PendaftaranAnggotaTetap $pendaftaranAnggotaTetap): void
    {
        //
    }

    /**
     * Handle the PendaftaranAnggotaTetap "updated" event.
     */
    public function updated(PendaftaranAnggotaTetap $pendaftaran): void
    {
        // Kirim notifikasi jika status pendaftaran berubah menjadi disetujui atau ditolak
        if ($pendaftaran->wasChanged('status')) {
            $newStatus = $pendaftaran->status;

            if ($newStatus === 'disetujui' || $newStatus === 'ditolak') {
                $pendaftaran->user->notify(new PendaftaranAnggotaTetapNotification($pendaftaran));
            }
        }
    }

    /**
     * Handle the PendaftaranAnggotaTetap "deleted" event.
     */
    public function deleted(PendaftaranAnggotaTetap $pendaftaranAnggotaTetap): void
    {
        //
    }

    /**
     * Handle the PendaftaranAnggotaTetap "restored" event.
     */
    public function restored(PendaftaranAnggotaTetap $pendaftaranAnggotaTetap): void
    {
        //
    }

    /**
     * Handle the PendaftaranAnggotaTetap "force deleted" event.
     */
    public function forceDeleted(PendaftaranAnggotaTetap $pendaftaranAnggotaTetap): void
    {
        //
    }
}
