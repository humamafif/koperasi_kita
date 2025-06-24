<?php

namespace App\Notifications;

use App\Models\Simpanan;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SimpananStatusNotification extends Notification
{
    use Queueable;

    protected $simpanan;

    public function __construct(Simpanan $simpanan)
    {
        $this->simpanan = $simpanan;
    }

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        $status = $this->simpanan->status;
        $jenisSimpanan = $this->getJenisSimpananText($this->simpanan->jenis);

        $message = (new MailMessage)
            ->subject("{$jenisSimpanan} - {$this->getStatusText($status)}");

        if ($status === 'disetujui') {
            $message->greeting("Pembayaran {$jenisSimpanan} Anda Disetujui!")
                ->line("Pembayaran {$jenisSimpanan} sebesar Rp " . number_format($this->simpanan->jumlah, 0, ',', '.') . " telah disetujui.")
                ->line("Tanggal Pembayaran: " . $this->simpanan->tanggal_pembayaran->format('d M Y'))
                ->action('Lihat Detail Simpanan', url('/anggota/simpanan'));
        } else {
            $message->greeting("Pembayaran {$jenisSimpanan} Anda Ditolak")
                ->line("Maaf, pembayaran {$jenisSimpanan} sebesar Rp " . number_format($this->simpanan->jumlah, 0, ',', '.') . " telah ditolak.")
                ->line("Alasan: {$this->simpanan->keterangan}")
                ->action('Ajukan Pembayaran Baru', url('/anggota/simpanan/create'));
        }

        return $message->line('Terima kasih telah menggunakan layanan Koperasi Kita!');
    }

    public function toDatabase($notifiable): array
    {
        $status = $this->simpanan->status;
        $jenisSimpanan = $this->getJenisSimpananText($this->simpanan->jenis);

        return [
            'simpanan_id' => $this->simpanan->id,
            'jenis' => $this->simpanan->jenis,
            'jumlah' => $this->simpanan->jumlah,
            'status' => $status,
            'message' => "Pembayaran {$jenisSimpanan} Anda telah {$this->getStatusText($status)}",
            'alasan' => $this->simpanan->keterangan,
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

    private function getJenisSimpananText($jenis): string
    {
        return match ($jenis) {
            'pokok' => 'Simpanan Pokok',
            'wajib' => 'Simpanan Wajib',
            'sukarela' => 'Simpanan Sukarela',
            default => 'Simpanan',
        };
    }
}
