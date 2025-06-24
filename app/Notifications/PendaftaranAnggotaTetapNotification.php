<?php

namespace App\Notifications;

use App\Models\PendaftaranAnggotaTetap;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PendaftaranAnggotaTetapNotification extends Notification
{
    use Queueable;

    protected $pendaftaran;

    public function __construct(PendaftaranAnggotaTetap $pendaftaran)
    {
        $this->pendaftaran = $pendaftaran;
    }

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        $status = $this->pendaftaran->status;

        $message = (new MailMessage)
            ->subject("Pendaftaran Anggota Tetap - {$this->getStatusText($status)}");

        if ($status === 'disetujui') {
            $simpananPokok = \App\Models\KoperasiSetting::getSimpananPokokAmount();
            $message->greeting("Selamat, Pendaftaran Anggota Tetap Anda Disetujui!")
                ->line("Pendaftaran Anda sebagai anggota tetap telah disetujui.")
                ->line("Anda sekarang dapat melakukan simpanan wajib dan sukarela.")
                ->line("Simpanan pokok Anda sebesar Rp " . number_format($simpananPokok, 0, ',', '.') . " telah tercatat.")
                ->action('Kelola Simpanan', url('/anggota/simpanan'));
        } else {
            $message->greeting("Pendaftaran Anggota Tetap Anda Ditolak")
                ->line("Maaf, pendaftaran Anda sebagai anggota tetap telah ditolak.")
                ->line("Alasan: {$this->pendaftaran->keterangan}")
                ->action('Ajukan Kembali', url('/anggota/daftar-anggota-tetap'));
        }

        return $message->line('Terima kasih telah menggunakan layanan Koperasi Kita!');
    }

    public function toDatabase($notifiable): array
    {
        $status = $this->pendaftaran->status;

        return [
            'pendaftaran_id' => $this->pendaftaran->id,
            'status' => $status,
            'message' => "Pendaftaran anggota tetap Anda telah {$this->getStatusText($status)}",
            'alasan' => $this->pendaftaran->keterangan,
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
