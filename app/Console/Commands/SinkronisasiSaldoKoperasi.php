<?php

namespace App\Console\Commands;

use App\Models\SaldoKoperasi;
use Illuminate\Console\Command;

class SinkronisasiSaldoKoperasi extends Command
{
    protected $signature = 'app:sinkronisasi-saldo-koperasi';
    protected $description = 'Sinkronisasi saldo koperasi berdasarkan data transaksi yang ada';

    public function handle()
    {
        $this->info('Memulai sinkronisasi saldo koperasi...');

        $saldoAwal = SaldoKoperasi::getSaldo();
        $this->info("Saldo sebelum sinkronisasi: Rp " . number_format($saldoAwal, 0, ',', '.'));

        try {
            $saldoAkhir = SaldoKoperasi::sinkronisasiSaldo();
            $this->info("Saldo setelah sinkronisasi: Rp " . number_format($saldoAkhir, 0, ',', '.'));

            $this->info('Sinkronisasi saldo koperasi berhasil.');
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Sinkronisasi saldo koperasi gagal: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
