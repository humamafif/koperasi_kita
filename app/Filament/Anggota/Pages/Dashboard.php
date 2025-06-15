<?php

namespace App\Filament\Anggota\Pages;

use App\Models\PendaftaranAnggotaTetap;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Notifications\Notification;
use Filament\Notifications\Actions\Action;
use Illuminate\Support\Facades\Auth;

class Dashboard extends BaseDashboard
{
    public function mount(): void
    {
        parent::mount();
        if (Auth::check() && !Auth::user()->hasRole('anggota_tetap')) {
            $pendingRegistration = PendaftaranAnggotaTetap::where('user_id', Auth::id())
                ->where('status', 'pending')
                ->first();

            if ($pendingRegistration) {
                Notification::make()
                    ->title('Pendaftaran Anggota Tetap Sedang Diproses')
                    ->body('Pendaftaran Anda sedang ditinjau oleh administrator. Kami akan memberi tahu Anda hasilnya segera.')
                    ->warning()
                    ->persistent()
                    ->send();
            } else {
                Notification::make()
                    ->title('Upgrade ke Anggota Tetap')
                    ->body('Akses fitur simpan pinjam dan rasakan manfaat lebih sebagai anggota tetap.')
                    ->actions([
                        Action::make('upgrade')
                            ->button()
                            ->url(route('filament.anggota.pages.daftar-anggota-tetap'))
                            ->label('Daftar Sekarang'),
                    ])
                    ->persistent()
                    ->send();
            }
        }
    }

    protected function getHeaderWidgets(): array
    {
        $widgets = [];

        // Tambahkan widget upgrade untuk anggota biasa
        if (Auth::check() && !Auth::user()->hasRole('anggota_tetap')) {
            $widgets[] = \App\Filament\Anggota\Widgets\UpgradeAnggotaWidget::class;
        }

        // Widgets lain jika perlu ditambahkan
        if (Auth::check() && Auth::user()->hasRole('anggota_tetap')) {
            $widgets[] = \App\Filament\Anggota\Widgets\SimpananStatsWidget::class;
            $widgets[] = \App\Filament\Anggota\Widgets\PinjamanStatsWidget::class;
            $widgets[] = \App\Filament\Anggota\Widgets\SHUStatsWidget::class;
            $widgets[] = \App\Filament\Anggota\Widgets\SaldoWidget::class;
        }

        return $widgets;
    }
}
