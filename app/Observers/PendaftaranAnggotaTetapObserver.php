<?php

namespace App\Observers;

use App\Models\KoperasiSetting;
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
        $simpananWajibAmount = KoperasiSetting::getSimpananWajibAmount();

        $cutoffDay = KoperasiSetting::getTagihanCutoffDay();
        $dueDayNewMember = KoperasiSetting::getTagihanDueDayNewMember();
        // Aturan:
        // - Jika tanggal pendaftaran < cutoff_day: buat tagihan bulan ini
        // - Jika tanggal pendaftaran >= cutoff_day: buat tagihan bulan depan

        if ($startDate->day < $cutoffDay) {
            // Pendaftaran tanggal < cutoff_day, tagihan bulan ini
            $dueDate = $startDate->copy()->setDay($dueDayNewMember);
            $alasan = "karena tanggal pendaftaran < {$cutoffDay}";
        } else {
            // Pendaftaran tanggal >= cutoff_day, tagihan bulan depan
            $dueDate = $startDate->copy()->addMonth()->setDay($dueDayNewMember);
            $alasan = "karena tanggal pendaftaran >= {$cutoffDay}";
        }

        // Khusus jika tanggal pendaftaran >= due_day, jatuh tempo sudah lewat, maka gunakan bulan depan
        if ($startDate->day >= $dueDayNewMember && $startDate->month == $dueDate->month && $startDate->year == $dueDate->year) {
            $dueDate = $dueDate->addMonth();
            $alasan = "karena tanggal pendaftaran >= {$dueDayNewMember} (tanggal jatuh tempo bulan ini sudah lewat)";
        }

        $periode = $dueDate->format('Y-m');

        $tagihanExists = TagihanAnggota::where('user_id', $pendaftaran->user_id)
            ->where('jenis_tagihan', 'simpanan_wajib')
            ->where('periode', $periode)
            ->exists();

        if (!$tagihanExists) {
            TagihanAnggota::create([
                'user_id' => $pendaftaran->user_id,
                'jenis_tagihan' => 'simpanan_wajib',
                'jumlah' => $simpananWajibAmount,
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
