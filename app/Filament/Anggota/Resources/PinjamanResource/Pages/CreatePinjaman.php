<?php

namespace App\Filament\Anggota\Resources\PinjamanResource\Pages;

use App\Filament\Anggota\Resources\PinjamanResource;
use App\Models\BungaPinjaman;
use App\Models\TenorPinjaman;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreatePinjaman extends CreateRecord
{
    protected static string $resource = PinjamanResource::class;
    protected static ?string $title = 'Pengajuan Pinjaman';

    protected function getFormActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Ajukan Pinjaman')
                ->icon('heroicon-o-plus')
                ->submit('create')
                ->successRedirectUrl(PinjamanResource::getUrl('index')),

            Action::make('cancel')
                ->outlined()
                ->url(PinjamanResource::getUrl('index'))
        ];
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = Auth::id();
        $data['status'] = 'pending';
        $data['tanggal_pengajuan'] = now();

        // Pastikan tenor_id diambil dan disimpan sebagai tenor_pinjaman_id
        if (isset($data['tenor_id'])) {
            $data['tenor_pinjaman_id'] = $data['tenor_id'];

            // Hitung dan simpan angsuran serta total bunga
            $tenor = TenorPinjaman::find($data['tenor_id']);
            if ($tenor && isset($data['jumlah'])) {
                $jumlah = $data['jumlah'];
                $durasi = $tenor->durasi;
                $bunga = $tenor->bunga;

                // Hitung total bunga dan angsuran
                $totalBunga = $jumlah * $bunga / 100 * ($durasi / 12);
                $pokok = $jumlah / $durasi;
                $bungaPerBulan = $totalBunga / $durasi;
                $angsuran = $pokok + $bungaPerBulan;

                // Simpan hasil perhitungan
                $data['total_bunga'] = $totalBunga;
                $data['angsuran_per_bulan'] = $angsuran;
            }

            // Hapus field yang tidak perlu
            unset($data['tenor_id']);
            unset($data['info_bunga']);
            unset($data['angsuran_bulanan']);
            unset($data['total_bayar']);
            unset($data['showSimulasi']);
        }

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
