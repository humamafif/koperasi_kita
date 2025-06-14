<?php

namespace App\Observers;

use App\Models\TagihanAnggota;
use App\Notifications\TagihanStatusNotification;

class TagihanAnggotaObserver
{
    public function updated(TagihanAnggota $tagihan): void
    {
        if ($tagihan->wasChanged('status')) {
            $newStatus = $tagihan->status;

            if (in_array($newStatus, ['menunggu_verifikasi', 'lunas'])) {
                $tagihan->user->notify(new TagihanStatusNotification($tagihan));
            }
        }
    }
}
