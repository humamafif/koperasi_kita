<?php

namespace App\Jobs;

use App\Models\TagihanAnggota;
use App\Notifications\TagihanReminderNotification;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendTagihanReminder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        // Ambil semua tagihan yang belum bayar dan jatuh tempo dalam 3 hari
        $dueDate = Carbon::now()->addDays(3)->toDateString();

        $tagihan = TagihanAnggota::where('status', 'belum_bayar')
            ->where('tanggal_jatuh_tempo', $dueDate)
            ->with('user')
            ->get();

        foreach ($tagihan as $bill) {
            // Kirim notifikasi ke anggota
            $bill->user->notify(new TagihanReminderNotification($bill));
        }
    }
}
