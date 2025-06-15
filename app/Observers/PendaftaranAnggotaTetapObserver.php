<?php

namespace App\Observers;

use App\Models\PendaftaranAnggotaTetap;
use App\Models\TagihanAnggota;
use App\Notifications\PendaftaranAnggotaTetapNotification;
use Illuminate\Support\Carbon;

class PendaftaranAnggotaTetapObserver
{
    /**
     * Handle the PendaftaranAnggotaTetap "created" event.
     */
    public function created(PendaftaranAnggotaTetap $pendaftaranAnggotaTetap): void {}

    /**
     * Handle the PendaftaranAnggotaTetap "updated" event.
     */
    public function updated(PendaftaranAnggotaTetap $pendaftaran): void
    {
        if ($pendaftaran->wasChanged('status')) {
            $newStatus = $pendaftaran->status;

            if ($newStatus === 'disetujui' || $newStatus === 'ditolak') {
                $pendaftaran->user->notify(new PendaftaranAnggotaTetapNotification($pendaftaran));
            }
            if ($newStatus === 'disetujui') {
                $this->createMandatorySavingBill($pendaftaran);
            }
        }
    }
    protected function createMandatorySavingBill(PendaftaranAnggotaTetap $pendaftaran): void
    {
        $startDate = $pendaftaran->tanggal_verifikasi;

        // Log untuk debugging
        \Illuminate\Support\Facades\Log::info("Membuat tagihan simpanan wajib pertama UDAH UPDATE", [
            'user_id' => $pendaftaran->user_id,
            'tanggal_verifikasi' => $startDate->format('Y-m-d'),
            'tanggal_day' => $startDate->day
        ]);

        // Aturan baru:
        // - Jika tanggal pendaftaran < 20: buat tagihan bulan ini (dengan jatuh tempo tanggal 25)
        // - Jika tanggal pendaftaran >= 20: buat tagihan bulan depan (dengan jatuh tempo tanggal 25)

        if ($startDate->day < 20) {
            // Pendaftaran tanggal < 20, tagihan bulan ini
            $dueDate = $startDate->copy()->setDay(25);
            $alasan = "karena tanggal pendaftaran < 20";
        } else {
            // Pendaftaran tanggal >= 20, tagihan bulan depan
            $dueDate = $startDate->copy()->addMonth()->setDay(25);
            $alasan = "karena tanggal pendaftaran >= 20";
        }

        // Khusus jika tanggal pendaftaran >= 25, jatuh tempo sudah lewat, maka gunakan bulan depan
        if ($startDate->day >= 25 && $startDate->month == $dueDate->month && $startDate->year == $dueDate->year) {
            $dueDate = $dueDate->addMonth();
            $alasan = "karena tanggal pendaftaran >= 25 (tanggal jatuh tempo bulan ini sudah lewat)";
        }

        $periode = $dueDate->format('Y-m');

        \Illuminate\Support\Facades\Log::info("Informasi pembuatan tagihan simpanan wajib", [
            'user_id' => $pendaftaran->user_id,
            'tanggal_verifikasi' => $startDate->format('Y-m-d'),
            'tanggal_jatuh_tempo' => $dueDate->format('Y-m-d'),
            'periode' => $periode,
            'alasan' => $alasan
        ]);

        $tagihanExists = TagihanAnggota::where('user_id', $pendaftaran->user_id)
            ->where('jenis_tagihan', 'simpanan_wajib')
            ->where('periode', $periode)
            ->exists();

        if (!$tagihanExists) {
            TagihanAnggota::create([
                'user_id' => $pendaftaran->user_id,
                'jenis_tagihan' => 'simpanan_wajib',
                'jumlah' => 50000, // Rp 50.000
                'tanggal_jatuh_tempo' => $dueDate,
                'status' => 'belum_bayar',
                'periode' => $periode,
                'keterangan' => "Simpanan wajib periode " . $dueDate->format('F Y'),
            ]);

            \Illuminate\Support\Facades\Log::info("Tagihan simpanan wajib berhasil dibuat", [
                'user_id' => $pendaftaran->user_id,
                'periode' => $periode,
                'tanggal_jatuh_tempo' => $dueDate->format('Y-m-d')
            ]);
        } else {
            \Illuminate\Support\Facades\Log::info("Tagihan simpanan wajib periode {$periode} sudah ada", [
                'user_id' => $pendaftaran->user_id
            ]);
        }
    }

    /**
     */
    public function deleted(PendaftaranAnggotaTetap $pendaftaranAnggotaTetap): void {}

    /**
     * Handle the PendaftaranAnggotaTetap "restored" event.
     */
    public function restored(PendaftaranAnggotaTetap $pendaftaranAnggotaTetap): void {}

    /**
     * Handle the PendaftaranAnggotaTetap "force deleted" event.
     */
    public function forceDeleted(PendaftaranAnggotaTetap $pendaftaranAnggotaTetap): void {}
}
