<?php
// filepath: d:\Development\menpro\koperasi_kita\app\Notifications\TagihanReminderNotification.php

namespace App\Notifications;

use App\Models\TagihanAnggota;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TagihanReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(protected TagihanAnggota $tagihan)
    {
        //
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $jenisTagihan = $this->tagihan->jenis_tagihan === 'simpanan_wajib'
            ? 'Simpanan Wajib'
            : 'Angsuran Pinjaman';

        return (new MailMessage)
            ->subject('Pengingat: Tagihan ' . $jenisTagihan . ' Akan Jatuh Tempo')
            ->greeting('Pengingat Tagihan ' . $jenisTagihan)
            ->line('Tagihan ' . $jenisTagihan . ' Anda akan jatuh tempo dalam 3 hari.')
            ->line('Jumlah: Rp ' . number_format($this->tagihan->jumlah, 0, ',', '.'))
            ->line('Tanggal Jatuh Tempo: ' . (\Carbon\Carbon::parse($this->tagihan->tanggal_jatuh_tempo))->format('d M Y'))
            ->action('Lihat Detail Tagihan', url('/anggota/tagihan-saya'))
            ->line('Mohon segera melakukan pembayaran untuk menghindari keterlambatan.');
    }

    public function toDatabase(object $notifiable): array
    {
        $jenisTagihan = $this->tagihan->jenis_tagihan === 'simpanan_wajib'
            ? 'Simpanan Wajib'
            : 'Angsuran Pinjaman';

        return [
            'tagihan_id' => $this->tagihan->id,
            'jumlah' => $this->tagihan->jumlah,
            'jenis_tagihan' => $this->tagihan->jenis_tagihan,
            'tanggal_jatuh_tempo' => $this->tagihan->tanggal_jatuh_tempo,
            'message' => "Pengingat: Tagihan {$jenisTagihan} Anda akan jatuh tempo pada " . (\Carbon\Carbon::parse($this->tagihan->tanggal_jatuh_tempo))->format('d M Y'),
        ];
    }
}
