<?php

namespace App\Filament\Resources\SHUDistributionResource\Pages;

use App\Filament\Resources\SHUDistributionResource;
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
                    $this->saldoKoperasi = (new SHUCalculationService)->getSaldoKoperasi();
                    $biayaOperasional = (float) ($state['biaya_operasional'] ?? 0);
                    $pajak = (float) ($state['pajak'] ?? 0);
                    $danaCadangan = (float) ($state['dana_cadangan'] ?? 0);
                    $biayaLainnya = (float) ($state['biaya_lainnya'] ?? 0);

                    // Hitung total biaya dan SHU
                    $totalBiaya = $biayaOperasional + $pajak + $danaCadangan + $biayaLainnya;
                    $totalSHU = $this->saldoKoperasi - $totalBiaya;

                    if ($totalSHU <= 0) {
                        Notification::make()
                            ->title('Error Perhitungan')
                            ->body('Total biaya melebihi saldo koperasi. Tidak ada SHU yang bisa dibagikan.')
                            ->danger()
                            ->send();
                        return;
                    }

                    // Perbarui formState dengan hasil perhitungan
                    $this->formState['total_biaya'] = $totalBiaya;
                    $this->formState['total_shu'] = $totalSHU;
                    $this->formState['show_simulasi'] = true;
                    $this->formState['saldo_koperasi'] = number_format($this->saldoKoperasi, 0, ',', '.');

                    // Ambil data simulasi
                    $jumlahAnggota = (new SHUCalculationService)->getJumlahAnggotaBerhakSHU();
                    $avgSimpanan = (new SHUCalculationService)->getRataRataSimpananAnggota();
                    $estimasiDistribusiRata = $jumlahAnggota > 0 ? $totalSHU / $jumlahAnggota : 0;

                    $this->formState['jumlah_anggota'] = $jumlahAnggota;
                    $this->formState['avg_simpanan'] = $avgSimpanan;
                    $this->formState['estimasi_distribusi_rata'] = $estimasiDistribusiRata;

                    // Fill form dengan semua data yang ada
                    $this->form->fill($this->formState);

                    Notification::make()
                        ->title('Perhitungan Selesai')
                        ->body("Total biaya: Rp " . number_format($totalBiaya, 0, ',', '.') .
                            "\nTotal SHU: Rp " . number_format($totalSHU, 0, ',', '.'))
                        ->success()
                        ->send();
                }),
        ];
    }
    public function mount(): void
    {
        parent::mount();

        // Set saldo koperasi saat awal
        $this->saldoKoperasi = (new SHUCalculationService)->getSaldoKoperasi();

        // Pre-fill saldo koperasi field dengan format
        $this->form->fill([
            'saldo_koperasi' => number_format($this->saldoKoperasi, 0, ',', '.'),
        ]);
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

            // Ambil saldo koperasi
            $saldoKoperasi = (float) $this->saldoKoperasi;
            if ($saldoKoperasi <= 0) {
                $saldoKoperasi = (new SHUCalculationService)->getSaldoKoperasi();
            }

            // Parse nilai input biaya
            $biayaOperasional = (float) $data['biaya_operasional'];
            $pajak = (float) $data['pajak'];
            $danaCadangan = (float) $data['dana_cadangan'];
            $biayaLainnya = (float) ($data['biaya_lainnya'] ?? 0);
            $keteranganBiaya = $data['keterangan_biaya'] ?? '';

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
            $calculatedTotalBiaya = $biayaOperasional + $pajak + $danaCadangan + $biayaLainnya;
            $calculatedTotalSHU = $saldoKoperasi - $calculatedTotalBiaya;

            if ($calculatedTotalSHU <= 0) {
                Notification::make()
                    ->title('Total biaya melebihi saldo koperasi')
                    ->body('Total biaya tidak boleh melebihi saldo koperasi. Silahkan sesuaikan biaya.')
                    ->danger()
                    ->send();

                DB::rollBack();
                return;
            }

            // Simpan ke tabel SHUBiaya
            $shuBiaya = SHUBiaya::create([
                'tahun' => $tahun,
                'saldo_koperasi' => $saldoKoperasi,
                'biaya_operasional' => $biayaOperasional,
                'pajak' => $pajak,
                'dana_cadangan' => $danaCadangan,
                'biaya_lainnya' => $biayaLainnya,
                'keterangan' => $keteranganBiaya,
                'total_biaya' => $calculatedTotalBiaya,
                'total_shu' => $calculatedTotalSHU,
                'tanggal' => now(),
            ]);

            // Hitung dan simpan SHU anggota menggunakan service
            $result = (new SHUCalculationService)->calculateSHU($tahun, $calculatedTotalSHU);

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

            Notification::make()
                ->title("Perhitungan SHU untuk tahun $tahun berhasil")
                ->body("Total SHU: Rp " . number_format($calculatedTotalSHU, 0, ',', '.') . " didistribusikan ke " . count($result['distribusi']) . " anggota")
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
        return [];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
