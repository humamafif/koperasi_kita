<?php

namespace App\Console\Commands;

use App\Models\Pinjaman;
use App\Models\TagihanAnggota;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class GenerateTagihanBulanan extends Command
{
    protected $signature = 'app:generate-tagihan-bulanan {--bulan=} {--tahun=} {--force}';
    protected $description = 'Generate tagihan bulanan untuk simpanan wajib dan angsuran pinjaman';

    public function handle()
    {
        // Menggunakan bulan berikutnya sebagai default
        // Karena biasanya generate dilakukan di akhir bulan untuk tagihan bulan depan
        $bulan = $this->option('bulan') ? (int) $this->option('bulan') : now()->addMonth()->month;
        $tahun = $this->option('tahun') ? (int) $this->option('tahun') : ($bulan === 1 ? now()->addYear()->year : now()->year);

        // Format periode YYYY-MM
        $periode = sprintf('%04d-%02d', $tahun, $bulan);
        // Ambil hari jatuh tempo dari setting
        $dueDayRegular = \App\Models\KoperasiSetting::getTagihanDueDayRegular();
        // Tanggal jatuh tempo: tanggal 10 bulan yang ditentukan
        $tanggalJatuhTempo = Carbon::createFromDate($tahun, $bulan, $dueDayRegular);

        $this->info("Memulai generate tagihan untuk periode $periode dengan jatuh tempo {$tanggalJatuhTempo->format('d M Y')}");

        // 1. Generate tagihan simpanan wajib untuk semua anggota tetap
        $this->info('Generating tagihan simpanan wajib...');

        $anggotaTetapRole = Role::where('name', 'anggota_tetap')->first();
        $anggotaTetapUsers = User::role($anggotaTetapRole)->get();

        $bar = $this->output->createProgressBar(count($anggotaTetapUsers));
        $bar->start();

        $tagihansCreated = 0;
        $tagihansSkipped = 0;

        foreach ($anggotaTetapUsers as $user) {
            // Cek apakah user ini sudah memiliki tagihan simpanan wajib untuk periode ini
            $tagihanExists = TagihanAnggota::where('user_id', $user->id)
                ->where('jenis_tagihan', 'simpanan_wajib')
                ->where('periode', $periode)
                ->exists();

            if (!$tagihanExists || $this->option('force')) {
                $simpananWajibAmount = \App\Models\KoperasiSetting::getSimpananWajibAmount();
                TagihanAnggota::updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'jenis_tagihan' => 'simpanan_wajib',
                        'periode' => $periode,
                    ],
                    [
                        'jumlah' => $simpananWajibAmount,
                        'tanggal_jatuh_tempo' => $tanggalJatuhTempo,
                        'status' => 'belum_bayar',
                        'keterangan' => "Tagihan simpanan wajib periode " . Carbon::createFromDate($tahun, $bulan, 1)->translatedFormat('F Y'),
                    ]
                );
                $tagihansCreated++;
            } else {
                $tagihansSkipped++;
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();

        $this->info("$tagihansCreated tagihan simpanan wajib dibuat, $tagihansSkipped tagihan dilewati (sudah ada)");

        $this->info('Generate tagihan bulanan selesai!');
        return 0;
    }
}
