<?php


namespace App\Filament\Widgets;

use App\Models\KoperasiSetting;
use App\Models\PembelianProduk;
use App\Models\Pinjaman;
use App\Models\SaldoKoperasi;
use App\Models\Simpanan;
use App\Models\TagihanAnggota;
use App\Models\TenorPinjaman;
use App\Models\User;
use Filament\Widgets\Widget;

class KoperasiFinanceDetailWidget extends Widget
{
    protected static string $view = 'filament.admin.widgets.koperasi-finance-detail-widget';
    protected static ?int $sort = 1;
    protected int|string|array $columnSpan = 'full';

    public function getPinjamanYangSudahDibayar()
    {
        $tagihanLunas = TagihanAnggota::where('jenis_tagihan', 'angsuran_pinjaman')
            ->where('status', 'lunas')
            ->whereNotNull('pinjaman_id')
            ->get();
        $tagihanLunasTotal = $tagihanLunas->sum('jumlah');
        return $tagihanLunasTotal;
    }



    public function getTotalSaldoKoperasi()
    {
        return  SaldoKoperasi::getSaldo();
    }

    public function getTotalSimpananPokokData()
    {
        $jumlah = Simpanan::where('status', 'disetujui')
            ->where('jenis', 'pokok')
            ->sum('jumlah');

        $anggota_count = Simpanan::where('status', 'disetujui')
            ->where('jenis', 'pokok')
            ->distinct('user_id')
            ->count('user_id');

        return [
            'jumlah' => $jumlah,
            'anggota_count' => $anggota_count
        ];
    }

    public function getTotalSimpananWajibData()
    {
        $jumlah = Simpanan::where('status', 'disetujui')
            ->where('jenis', 'wajib')
            ->sum('jumlah');

        $anggota_count = Simpanan::where('status', 'disetujui')
            ->where('jenis', 'wajib')
            ->distinct('user_id')
            ->count('user_id');

        $transaksi_count = Simpanan::where('status', 'disetujui')
            ->where('jenis', 'wajib')
            ->count();

        return [
            'jumlah' => $jumlah,
            'anggota_count' => $anggota_count,
            'transaksi_count' => $transaksi_count
        ];
    }

    public function getTotalSimpananSukarelaData()
    {
        $jumlah = Simpanan::where('status', 'disetujui')
            ->where('jenis', 'sukarela')
            ->sum('jumlah');

        $anggota_count = Simpanan::where('status', 'disetujui')
            ->where('jenis', 'sukarela')
            ->distinct('user_id')
            ->count('user_id');

        $transaksi_count = Simpanan::where('status', 'disetujui')
            ->where('jenis', 'sukarela')
            ->count();
        return [
            'jumlah' => $jumlah,
            'anggota_count' => $anggota_count,
            'transaksi_count' => $transaksi_count
        ];
    }

    public function getBiayaAdmin()
    {
        return KoperasiSetting::getBiayaAdminPercentDisplay();
    }

    public function getBiayaAdminData()
    {
        $jumlah = PembelianProduk::where('status', 'selesai')
            ->sum('biaya_admin');

        $transaksi_count = PembelianProduk::where('status', 'selesai')
            ->count();

        return [
            'jumlah' => $jumlah,
            'transaksi_count' => $transaksi_count
        ];
    }

    public function getBungaPinjamanData()
    {
        $jumlah = $this->getTotalBungaTerbayar();

        $transaksi_count = TagihanAnggota::where('jenis_tagihan', 'angsuran_pinjaman')
            ->where('status', 'lunas')
            ->count();

        $pinjaman_count = TagihanAnggota::where('jenis_tagihan', 'angsuran_pinjaman')
            ->where('status', 'lunas')
            ->distinct('pinjaman_id')
            ->count('pinjaman_id');

        return [
            'jumlah' => $jumlah,
            'transaksi_count' => $transaksi_count,
            'pinjaman_count' => $pinjaman_count
        ];
    }

    protected function getTotalBungaTerbayar()
    {
        $tagihanLunas = TagihanAnggota::where('jenis_tagihan', 'angsuran_pinjaman')
            ->where('status', 'lunas')
            ->whereNotNull('pinjaman_id')
            ->get();
        $totalBungaTerbayar = 0;
        foreach ($tagihanLunas as $tagihan) {
            $pinjaman = Pinjaman::find($tagihan->pinjaman_id);
            if ($pinjaman && $pinjaman->total_bunga > 0) {
                $totalBunga = $pinjaman->total_bunga;
                $tenorPinjaman = TenorPinjaman::find($pinjaman->tenor_pinjaman_id);
                $tenor = $tenorPinjaman ? $tenorPinjaman->durasi : 0;
                if ($tenor > 0) {
                    $bungaPerAngsuran = $totalBunga / $tenor;
                    $totalBungaTerbayar += $bungaPerAngsuran;
                }
            }
        }
        return $totalBungaTerbayar;
    }

    public function getAnggotaTetapCount()
    {
        return User::whereHas('roles', function ($query) {
            $query->where('name', 'anggota_tetap');
        })->count();
    }
}
