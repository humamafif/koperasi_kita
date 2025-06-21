<?php

namespace App\Filament\Resources\SHUDistributionResource\Pages;

use App\Filament\Resources\SHUDistributionResource;
use App\Models\SaldoKoperasi;
use App\Models\SHUBiaya;
use App\Models\SHUDistribution;
use App\Services\SHUCalculationService;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CreateSHUDistribution extends CreateRecord
{
    protected static string $resource = SHUDistributionResource::class;
    protected static ?string $title = 'Hitung SHU Baru';
    protected static bool $canCreateAnother = false;

    public $formState = [];
    public $saldoKoperasi = 0;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('hitungSHU')
                ->label('Hitung')
                ->color('primary')
                ->icon('heroicon-o-calculator')
                ->action(function () {
                    // Ambil seluruh data form saat ini
                    $state = $this->form->getState();

                    // Simpan data form ke property formState
                    $this->formState = $state;

                    // Ambil nilai-nilai yang dibutuhkan untuk perhitungan
                    $shuService = new SHUCalculationService();
                    $totalBiayaAdmin = $shuService->getTotalBiayaAdmin();
                    $totalBungaPinjaman = $shuService->getTotalBungaPinjaman();
                    $totalSimpanan = $shuService->getTotalSimpanan();

                    // Total saldo koperasi berdasarkan formula baru
                    $this->saldoKoperasi = SaldoKoperasi::getSaldo();

                    $biayaOperasional = (float) ($state['biaya_operasional'] ?? 0);
                    $pajak = (float) ($state['pajak'] ?? 0);
                    $biayaLainnya = (float) ($state['biaya_lainnya'] ?? 0);

                    // Hitung total biaya
                    $totalBiaya = $biayaOperasional + $pajak + $biayaLainnya;

                    // Hitung total SHU berdasarkan formula baru
                    $totalSHU = $this->saldoKoperasi - $totalBiaya;

                    if ($totalSHU <= 0) {
                        Notification::make()
                            ->title('Error Perhitungan')
                            ->body('Total biaya melebihi saldo koperasi. Tidak ada SHU yang bisa dibagikan.')
                            ->danger()
                            ->send();
                        return;
                    }

                    // Hitung dana cadangan berdasarkan persentase
                    $persentaseDanaCadangan = (float) ($state['persentase_dana_cadangan'] ?? 40);
                    $danaCadangan = $totalSHU * ($persentaseDanaCadangan / 100);

                    // Perbarui formState dengan hasil perhitungan
                    $this->formState['total_biaya'] = $totalBiaya;
                    $this->formState['total_shu'] = $totalSHU;
                    $this->formState['dana_cadangan'] = $danaCadangan;
                    $this->formState['show_simulasi'] = true;
                    $this->formState['saldo_koperasi'] = number_format($this->saldoKoperasi, 0, ',', '.');

                    // Ambil data simulasi
                    $jumlahAnggota = $shuService->getJumlahAnggotaBerhakSHU();
                    $avgSimpanan = $shuService->getRataRataSimpananAnggota();
                    $estimasiDistribusiRata = $jumlahAnggota > 0 ? $totalSHU * (1 - $persentaseDanaCadangan / 100) / $jumlahAnggota : 0;

                    $this->formState['jumlah_anggota'] = $jumlahAnggota;
                    $this->formState['avg_simpanan'] = $avgSimpanan;
                    $this->formState['estimasi_distribusi_rata'] = $estimasiDistribusiRata;

                    // Fill form dengan semua data yang ada
                    $this->form->fill($this->formState);

                    Notification::make()
                        ->title('Perhitungan Selesai')
                        ->body("Total biaya: Rp " . number_format($totalBiaya, 0, ',', '.') .
                            "\nTotal SHU: Rp " . number_format($totalSHU, 0, ',', '.') .
                            "\nDana Cadangan: Rp " . number_format($danaCadangan, 0, ',', '.'))
                        ->success()
                        ->send();
                }),
        ];
    }


    public function mount(): void
    {
        parent::mount();

        // Set saldo koperasi saat awal dengan perhitungan baru
        $shuService = new SHUCalculationService();
        $totalBiayaAdmin = $shuService->getTotalBiayaAdmin();
        $totalBungaPinjaman = $shuService->getTotalBungaPinjaman();
        $totalSimpanan = $shuService->getTotalSimpanan();

        $this->saldoKoperasi = SaldoKoperasi::getSaldo();

        // Pre-fill saldo koperasi field dengan format
        $this->form->fill([
            'saldo_koperasi' => number_format($this->saldoKoperasi, 0, ',', '.'),
            'persentase_dana_cadangan' => 40,
            'persentase_jasa_usaha' => 20,
            'persentase_jasa_modal' => 20,
            'persentase_jasa_pinjaman' => 20,
        ]);

        // Analisis komponen SHU aktif
        $componentAnalysis = $shuService->getActiveComponentsAnalysis();

        // Tampilkan notifikasi jika ada komponen yang tidak aktif
        $inactiveComponents = [];
        foreach ($componentAnalysis as $name => $component) {
            if (!$component['aktif']) {
                $inactiveComponents[] = str_replace('_', ' ', ucfirst($name)) . ' (' . $component['keterangan'] . ')';
            }
        }

        if (!empty($inactiveComponents)) {
            Notification::make()
                ->title('Komponen SHU Tidak Aktif')
                ->body('Beberapa komponen SHU tidak aktif dan persentasenya akan didistribusikan secara proporsional: ' . implode(', ', $inactiveComponents))
                ->warning()
                ->persistent()
                ->send();
        }
    }

    public function create(bool $another = false): void
    {
        $data = $this->form->getState();
        $tahun = (int) $data['tahun'];

        // Cek apakah tahun ini sudah ada perhitungan SHU
        if (SHUDistribution::where('tahun', $tahun)->exists()) {
            Notification::make()
                ->title("Perhitungan SHU untuk tahun $tahun sudah ada")
                ->body("Silahkan batalkan terlebih dahulu untuk menghitung ulang.")
                ->danger()
                ->send();

            $this->redirect($this->getResource()::getUrl('index'));
            return;
        }

        try {
            DB::beginTransaction();

            // Parse nilai input
            $biayaOperasional = (float) $data['biaya_operasional'];
            $pajak = (float) $data['pajak'];
            $biayaLainnya = (float) ($data['biaya_lainnya'] ?? 0);
            $keteranganBiaya = $data['keterangan_biaya'] ?? '';

            // Persentase distribusi SHU
            $persentaseDanaCadangan = (float) $data['persentase_dana_cadangan'];
            $persentaseJasaUsaha = (float) $data['persentase_jasa_usaha'];
            $persentaseJasaModal = (float) $data['persentase_jasa_modal'];
            $persentaseJasaPinjaman = (float) $data['persentase_jasa_pinjaman'];

            // Validasi total persentase harus 100%
            $totalPersentase = $persentaseDanaCadangan + $persentaseJasaUsaha + $persentaseJasaModal + $persentaseJasaPinjaman;
            if (abs($totalPersentase - 100) > 0.01) { // Allowing small floating point differences
                Notification::make()
                    ->title('Error Persentase')
                    ->body('Total persentase harus 100%. Saat ini: ' . number_format($totalPersentase, 2) . '%')
                    ->danger()
                    ->send();

                DB::rollBack();
                return;
            }

            if (!isset($data['total_biaya']) || !isset($data['total_shu']) || (float) $data['total_shu'] <= 0) {
                Notification::make()
                    ->title('Simulasi Belum Dilakukan')
                    ->body('Silahkan klik tombol "Hitung" terlebih dahulu untuk menghitung distribusi SHU.')
                    ->warning()
                    ->send();

                DB::rollBack();
                return;
            }

            // Hitung ulang untuk memastikan nilai yang benar
            $shuService = new SHUCalculationService();
            $totalBiayaAdmin = $shuService->getTotalBiayaAdmin();
            $totalBungaPinjaman = $shuService->getTotalBungaPinjaman();
            $totalSimpanan = $shuService->getTotalSimpanan();

            $saldoKoperasi = SaldoKoperasi::getSaldo();
            $calculatedTotalBiaya = $biayaOperasional + $pajak + $biayaLainnya;
            $calculatedTotalSHU = $saldoKoperasi - $calculatedTotalBiaya;
            $calculatedDanaCadangan = $calculatedTotalSHU * ($persentaseDanaCadangan / 100);

            if ($calculatedTotalSHU <= 0) {
                Notification::make()
                    ->title('Total biaya melebihi saldo koperasi')
                    ->body('Total biaya tidak boleh melebihi saldo koperasi. Silahkan sesuaikan biaya.')
                    ->danger()
                    ->send();

                DB::rollBack();
                return;
            }

            // Simpan ke tabel SHUBiaya dengan persentase baru
            $shuBiaya = SHUBiaya::create([
                'tahun' => $tahun,
                'saldo_koperasi' => $saldoKoperasi,
                'biaya_operasional' => $biayaOperasional,
                'pajak' => $pajak,
                'dana_cadangan' => $calculatedDanaCadangan,
                'biaya_lainnya' => $biayaLainnya,
                'keterangan_biaya' => $keteranganBiaya,
                'total_biaya' => $calculatedTotalBiaya,
                'total_shu' => $calculatedTotalSHU,
                'persentase_dana_cadangan' => $persentaseDanaCadangan,
                'persentase_jasa_usaha' => $persentaseJasaUsaha,
                'persentase_jasa_modal' => $persentaseJasaModal,
                'persentase_jasa_pinjaman' => $persentaseJasaPinjaman,
            ]);

            // Hitung dan simpan SHU anggota menggunakan service dengan parameter persentase baru
            $result = (new SHUCalculationService)->calculateSHU(
                $tahun,
                $calculatedTotalSHU,
                $persentaseJasaUsaha,
                $persentaseJasaModal,
                $persentaseJasaPinjaman
            );

            if (!$result['success']) {
                DB::rollBack();
                Notification::make()
                    ->title("Perhitungan SHU gagal")
                    ->body($result['message'])
                    ->danger()
                    ->send();

                return;
            }

            // Commit hanya jika semua proses berhasil
            DB::commit();

            // Buat pesan sukses dengan informasi redistribusi
            $successMessage = "Total SHU: Rp " . number_format($calculatedTotalSHU, 0, ',', '.') .
                " didistribusikan ke " . count($result['distribusi']) . " anggota";

            // Tambahkan informasi redistribusi jika ada
            if (isset($result['redistribusi_info']) && $result['redistribusi_info']['persentase_tidak_terpakai'] > 0) {
                $successMessage .= "\n\nRedistribusi persentase yang tidak terpakai (" .
                    number_format($result['redistribusi_info']['persentase_tidak_terpakai'], 2) . "%):" .
                    "\n- Jasa usaha: " . number_format($result['redistribusi_info']['persentase_jasa_usaha_adjusted'], 2) . "%" .
                    "\n- Jasa modal: " . number_format($result['redistribusi_info']['persentase_jasa_modal_adjusted'], 2) . "%" .
                    "\n- Jasa pinjaman: " . number_format($result['redistribusi_info']['persentase_jasa_pinjaman_adjusted'], 2) . "%";

                $shuBiaya->update([
                    'persentase_jasa_usaha_adjusted' => $result['redistribusi_info']['persentase_jasa_usaha_adjusted'],
                    'persentase_jasa_modal_adjusted' => $result['redistribusi_info']['persentase_jasa_modal_adjusted'],
                    'persentase_jasa_pinjaman_adjusted' => $result['redistribusi_info']['persentase_jasa_pinjaman_adjusted'],
                ]);
            }

            Notification::make()
                ->title("Perhitungan SHU untuk tahun $tahun berhasil")
                ->body($successMessage)
                ->success()
                ->send();

            $this->redirect($this->getResource()::getUrl('index'));
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error saat menghitung SHU: " . $e->getMessage());
            Log::error("Error class: " . get_class($e));
            Log::error($e->getTraceAsString());

            Notification::make()
                ->title("Error saat menghitung SHU")
                ->body("Error code: " . $e->getCode() . "\n" . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    protected function cancel()
    {
        $this->redirect($this->getResource()::getUrl('index'));
    }
}
