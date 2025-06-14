<?php

namespace App\Notifications;

use App\Models\TagihanAnggota;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TagihanStatusNotification extends Notification implements ShouldQueue
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
        $message = new MailMessage;

        $jenisTagihan = $this->tagihan->jenis_tagihan === 'simpanan_wajib'
            ? 'Simpanan Wajib'
            : 'Angsuran Pinjaman';

        if ($this->tagihan->status === 'lunas') {
            $message->greeting("Pembayaran {$jenisTagihan} Anda Telah Diverifikasi")
                ->line("Pembayaran {$jenisTagihan} sebesar Rp " . number_format($this->tagihan->jumlah, 0, ',', '.') . " telah diverifikasi.")
                ->line("Tanggal Pembayaran: " . $this->tagihan->tanggal_pembayaran?->format('d M Y'))
                ->action('Lihat Detail Tagihan', url('/anggota/tagihan-saya'));
        } else {
            $message->greeting("{$jenisTagihan} Anda Sedang Diverifikasi")
                ->line("Pembayaran {$jenisTagihan} sebesar Rp " . number_format($this->tagihan->jumlah, 0, ',', '.') . " sedang dalam proses verifikasi.")
                ->line("Tanggal Pembayaran: " . $this->tagihan->tanggal_pembayaran?->format('d M Y'))
                ->action('Lihat Detail Tagihan', url('/anggota/tagihan-saya'));
        }

        return $message->line('Terima kasih telah menggunakan layanan Koperasi Kita!');
    }

    public function toDatabase(object $notifiable): array
    {
        $jenisTagihan = $this->tagihan->jenis_tagihan === 'simpanan_wajib'
            ? 'Simpanan Wajib'
            : 'Angsuran Pinjaman';

        $status = $this->tagihan->status;

        return [
            'tagihan_id' => $this->tagihan->id,
            'jumlah' => $this->tagihan->jumlah,
            'jenis_tagihan' => $this->tagihan->jenis_tagihan,
            'status' => $status,
            'message' => "Pembayaran {$jenisTagihan} Anda telah " . ($status === 'lunas' ? 'diverifikasi' : 'diterima dan sedang diproses'),
        ];
    }
}
