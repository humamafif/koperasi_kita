<?php

namespace App\Notifications;

use App\Models\Pinjaman;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PinjamanStatusUpdated extends Notification
{
    use Queueable;

    protected $pinjaman;

    public function __construct(Pinjaman $pinjaman)
    {
        $this->pinjaman = $pinjaman;
    }

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        $status = $this->pinjaman->status;

        $message = (new MailMessage)
            ->subject("Pengajuan Pinjaman - {$this->getStatusText($status)}");

        if ($status === 'disetujui') {
            $message->greeting("Selamat, Pengajuan Pinjaman Anda Disetujui!")
                ->line("Pengajuan pinjaman sebesar Rp " . number_format($this->pinjaman->jumlah, 0, ',', '.') . " telah disetujui.")
                ->line("Tenor: {$this->pinjaman->durasi_tenor} bulan")
                ->line("Bunga: {$this->pinjaman->nilai_bunga}% per tahun")
                ->line("Angsuran per Bulan: Rp " . number_format($this->pinjaman->angsuran_per_bulan, 0, ',', '.'))
                ->line("Tanggal Persetujuan: " . $this->pinjaman->tanggal_persetujuan->format('d M Y'))
                ->action('Lihat Detail Pinjaman', url('/anggota/pinjaman/' . $this->pinjaman->id));
        } else {
            $message->greeting("Pengajuan Pinjaman Anda Ditolak")
                ->line("Maaf, pengajuan pinjaman sebesar Rp " . number_format($this->pinjaman->jumlah, 0, ',', '.') . " telah ditolak.")
                ->line("Alasan: {$this->pinjaman->alasan_penolakan}")
                ->action('Ajukan Pinjaman Baru', url('/anggota/pinjaman/create'));
        }

        return $message->line('Terima kasih telah menggunakan layanan Koperasi Kita!');
    }

    public function toDatabase($notifiable): array
    {
        $status = $this->pinjaman->status;

        return [
            'pinjaman_id' => $this->pinjaman->id,
            'jumlah' => $this->pinjaman->jumlah,
            'status' => $status,
            'message' => "Pengajuan pinjaman Anda telah {$this->getStatusText($status)}",
            'alasan' => $this->pinjaman->alasan_penolakan,
        ];
    }

    private function getStatusText($status): string
    {
        return match ($status) {
            'disetujui' => 'DISETUJUI',
            'ditolak' => 'DITOLAK',
            default => strtoupper($status),
        };
    }
}
