<?php

namespace App\Filament\Anggota\Pages;

use App\Models\RiwayatTransaksi as model;
use Filament\Pages\Page;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class RiwayatTransaksi extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-clock';
    protected static ?string $navigationLabel = 'Riwayat Transaksi';
    protected static ?string $title = 'Riwayat Transaksi';
    protected static ?int $navigationSort = 4;

    protected static string $view = 'filament.anggota.pages.riwayat-transaksi';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                model::query()
                    ->where('user_id', Auth::id())
                    ->latest('tanggal')
            )
            ->columns([
                TextColumn::make('tanggal')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),

                BadgeColumn::make('jenis_transaksi')
                    ->label('Jenis Transaksi')
                    ->formatStateUsing(function (string $state): string {
                        return match ($state) {
                            'simpanan_pokok' => 'Simpanan Pokok',
                            'simpanan_wajib' => 'Simpanan Wajib',
                            'simpanan_sukarela' => 'Simpanan Sukarela',
                            'pinjaman' => 'Pengajuan Pinjaman',
                            'pembayaran_pinjaman' => 'Pembayaran Pinjaman',
                            default => ucfirst($state),
                        };
                    })
                    ->colors([
                        'primary' => fn($state) => str_contains($state, 'simpanan'),
                        'info' => fn($state) => $state === 'pinjaman',
                        'success' => fn($state) => $state === 'pembayaran_pinjaman',
                        'gray' => fn() => true,
                    ]),

                TextColumn::make('jumlah')
                    ->label('Jumlah')
                    ->money('IDR')
                    ->sortable(),

                BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'disetujui',
                        'danger' => 'ditolak',
                    ]),

                TextColumn::make('keterangan')
                    ->label('Keterangan')
                    ->limit(30)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        if (!$state || strlen($state) <= 30) {
                            return null;
                        }
                        return $state;
                    }),
            ])
            ->filters([
                // Filter yang sudah ada
                SelectFilter::make('jenis_transaksi')
                    ->options([
                        'simpanan_pokok' => 'Simpanan Pokok',
                        'simpanan_wajib' => 'Simpanan Wajib',
                        'simpanan_sukarela' => 'Simpanan Sukarela',
                        'pinjaman' => 'Pengajuan Pinjaman',
                        'pembayaran_pinjaman' => 'Pembayaran Pinjaman',
                    ]),

                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'disetujui' => 'Disetujui',
                        'ditolak' => 'Ditolak',
                    ]),

                Filter::make('tanggal')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('dari_tanggal'),
                        \Filament\Forms\Components\DatePicker::make('sampai_tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['dari_tanggal'],
                                fn(Builder $query, $date): Builder => $query->where('tanggal', '>=', $date),
                            )
                            ->when(
                                $data['sampai_tanggal'],
                                fn(Builder $query, $date): Builder => $query->where('tanggal', '<=', $date),
                            );
                    })
            ])
            ->recordAction(null)
            ->defaultSort('tanggal', 'desc');
    }

    public function getViewData(): array
    {
        $userId = Auth::id();

        $totalSimpanan = model::where('user_id', $userId)
            ->where('status', 'disetujui')
            ->whereIn('jenis_transaksi', ['simpanan_pokok', 'simpanan_wajib', 'simpanan_sukarela'])
            ->sum('jumlah');

        $totalPinjaman = model::where('user_id', $userId)
            ->where('status', 'disetujui')
            ->where('jenis_transaksi', 'pinjaman')
            ->sum('jumlah');

        $countTransaksi = model::where('user_id', $userId)->count();

        return [
            'totalSimpanan' => $totalSimpanan,
            'totalPinjaman' => $totalPinjaman,
            'countTransaksi' => $countTransaksi,
        ];
    }
}
