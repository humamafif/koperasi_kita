<?php

namespace App\Console\Commands;

use App\Models\Pinjaman;
use App\Models\TagihanAnggota;
use Carbon\Carbon;
use Illuminate\Console\Command;

class RecalculateLoanInterests extends Command
{
    protected $signature = 'app:recalculate-loan-interests';
    protected $description = 'Recalculates loan installments and interest for all approved loans';

    public function handle()
    {
        $this->info('Starting recalculation of loan interests...');
        $loans = Pinjaman::where('status', 'disetujui')->get();
        $this->info("Found {$loans->count()} approved loans");

        $progressBar = $this->output->createProgressBar($loans->count());
        $progressBar->start();

        foreach ($loans as $loan) {
            $this->recalculateLoan($loan);
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine();

        $this->info('Loan interest recalculation completed.');

        return Command::SUCCESS;
    }

    protected function recalculateLoan(Pinjaman $loan)
    {
        if (!$loan->tenorPinjaman) {
            $this->warn("Loan ID {$loan->id} has no tenor defined, skipping.");
            return;
        }

        $tenor = $loan->tenorPinjaman->durasi;
        $bunga = $loan->tenorPinjaman->bunga;

        $amount = $loan->jumlah;
        $principal = $amount / $tenor;
        $interestPerMonth = ($amount * $bunga / 100) / $tenor;
        $installment = $principal + $interestPerMonth;


        $totalInterest = $amount * $bunga / 100 * ($tenor / 12);

        $loan->update([
            'total_bunga' => $totalInterest,
            'angsuran_per_bulan' => $installment
        ]);


        $tagihan = TagihanAnggota::where('pinjaman_id', $loan->id)
            ->where('jenis_tagihan', 'angsuran_pinjaman')
            ->get();

        foreach ($tagihan as $invoice) {
            if ($invoice->jumlah != $installment) {
                $invoice->update([
                    'jumlah' => $installment
                ]);
            }
        }
    }
}
