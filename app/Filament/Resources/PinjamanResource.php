<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PinjamanResource\Pages;
use App\Filament\Resources\PinjamanResource\RelationManagers;
use App\Models\Pinjaman;
use App\Models\RiwayatTransaksi;
use App\Models\SaldoKoperasi;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PinjamanResource extends Resource
{
    protected static ?string $model = Pinjaman::class;
    protected static ?string $navigationGroup = 'Simpanan & Pinjaman';
    protected static ?int $navigationGroupSort = 1;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel = 'Persetujuan Pinjaman';
    protected static ?string $pluralModelLabel = 'pinjaman';
    protected static ?string $slug = 'pinjaman';
    protected static ?int $navigationSort = 2;

    public static function getNavigationBadge(): ?string
    {
        $count = Pinjaman::where('status', "pending")->count();
        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function canCreate(): bool
    {
        return false;
    }
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Detail Peminjam')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nama Anggota')
                            ->disabled()
                            ->afterStateHydrated(function ($component, $state, $record) {
                                if ($record && $record->user) {
                                    $component->state($record->user->name);
                                }
                            }),
                        Forms\Components\TextInput::make('email')
                            ->label('Email Anggota')
                            ->disabled()
                            ->afterStateHydrated(function ($component, $state, $record) {
                                if ($record && $record->user) {
                                    $component->state($record->user->email);
                                }
                            }),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Informasi Pinjaman')
                    ->schema([
                        Forms\Components\TextInput::make('jumlah')
                            ->label('Jumlah Pinjaman')
                            ->disabled()
                            ->prefix('Rp'),

                        Forms\Components\TextInput::make('nama')
                            ->label('Tenor Pinjaman')
                            ->disabled()
                            ->afterStateHydrated(function ($component, $state, $record) {
                                if ($record && $record->tenorPinjaman) {
                                    $component->state($record->tenorPinjaman->nama);
                                }
                            }),
                        Forms\Components\TextInput::make('bunga')
                            ->label('Margin Pinjaman')
                            ->disabled()
                            ->suffix('%')
                            ->afterStateHydrated(function ($component, $state, $record) {
                                if ($record && $record->tenorPinjaman) {
                                    $component->state($record->tenorPinjaman->bunga);
                                }
                            }),

                        Forms\Components\TextInput::make('tujuan')
                            ->label('Tujuan Pinjaman')
                            ->disabled(),

                        Forms\Components\Textarea::make('keterangan')
                            ->label('Keterangan')
                            ->disabled()
                            ->columnSpanFull(),

                        Forms\Components\DatePicker::make('tanggal_pengajuan')
                            ->label('Tanggal Pengajuan')
                            ->disabled(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Informasi Simulasi')
                    ->schema([
                        Forms\Components\TextInput::make('angsuran_per_bulan')
                            ->label('Angsuran per Bulan')
                            ->disabled()
                            ->prefix('Rp')
                            ->formatStateUsing(fn(Pinjaman $record): string => number_format($record->angsuran_per_bulan, 0, ',', '.')),

                        Forms\Components\TextInput::make('total_pembayaran')
                            ->label('Total Pembayaran')
                            ->disabled()
                            ->prefix('Rp')
                            ->formatStateUsing(fn(Pinjaman $record): string => number_format($record->total_pembayaran, 0, ',', '.')),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Persetujuan')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->required()
                            ->options([
                                'pending' => 'Pending',
                                'disetujui' => 'Disetujui',
                                'ditolak' => 'Ditolak',
                            ])
                            ->default('pending'),

                        Forms\Components\Textarea::make('alasan_penolakan')
                            ->label('Alasan Penolakan (jika ditolak)')
                            ->required(fn(Forms\Get $get): bool => $get('status') === 'ditolak')
                            ->hidden(fn(Forms\Get $get): bool => $get('status') !== 'ditolak'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Nama Anggota')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('tanggal_pengajuan')
                    ->label('Tanggal Pengajuan')
                    ->date('d M Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('jumlah')
                    ->label('Jumlah Pinjaman')
                    ->money('IDR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('tenorPinjaman.nama')
                    ->label('Tenor')
                    ->description(fn(Pinjaman $record): string => 'Bunga: ' . $record->nilai_bunga . '%')
                    ->sortable(),

                Tables\Columns\TextColumn::make('angsuran_per_bulan')
                    ->label('Angsuran/Bulan')
                    ->money('IDR'),

                Tables\Columns\TextColumn::make('tujuan')
                    ->label('Tujuan'),

                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'disetujui',
                        'danger' => 'ditolak',
                    ]),

                Tables\Columns\TextColumn::make('alasan_penolakan')
                    ->label('Alasan Penolakan')

            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'disetujui' => 'Disetujui',
                        'ditolak' => 'Ditolak',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('approve')
                    ->label('Setujui')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->visible(fn(Pinjaman $record) => $record->status === 'pending')
                    ->requiresConfirmation()
                    ->modalHeading('Konfirmasi Persetujuan Pinjaman')
                    ->modalDescription(fn(Pinjaman $record) => 'Dengan menyetujui pinjaman ini, dana sebesar Rp ' . number_format($record->jumlah, 0, ',', '.') . ' akan dicairkan dari saldo koperasi.')
                    ->modalSubmitActionLabel('Ya, Setujui Pinjaman')
                    ->action(function (Pinjaman $record) {
                        $saldoKoperasi = SaldoKoperasi::getSaldo();
                        if ($saldoKoperasi < $record->jumlah) {
                            Notification::make()
                                ->title('Saldo Koperasi Tidak Mencukupi')
                                ->body("Saldo koperasi saat ini: Rp " . number_format($saldoKoperasi, 0, ',', '.') .
                                    ", dibutuhkan: Rp " . number_format($record->jumlah, 0, ',', '.'))
                                ->danger()
                                ->send();
                            return;
                        }

                        DB::beginTransaction();
                        try {
                            // Kurangi saldo koperasi
                            $saldoBaru = SaldoKoperasi::kurang($record->jumlah);
                            // Update status pinjaman
                            $record->update([
                                'status' => 'disetujui',
                                'tanggal_persetujuan' => now(),
                                'disetujui_oleh' => Auth::user()->name,
                            ]);

                            // Update status riwayat transaksi
                            RiwayatTransaksi::where('referensi_id', $record->id)
                                ->where('referensi_tipe', 'App\\Models\\Pinjaman')
                                ->where('jenis_transaksi', 'pinjaman')
                                ->where('status', 'pending')
                                ->update([
                                    'status' => 'disetujui',
                                    'keterangan' => 'Pinjaman disetujui sebesar Rp ' . number_format($record->jumlah, 0, ',', '.')
                                ]);

                            // Hitung ulang financials
                            if (!$record->tenorPinjaman) {
                                throw new \Exception("Tenor not found for loan #{$record->id}");
                            }

                            $tenor = $record->tenorPinjaman->durasi;
                            $bunga = $record->tenorPinjaman->bunga;
                            $jumlah = $record->jumlah;

                            $totalBunga = $jumlah * $bunga / 100 * ($tenor / 12);
                            $pokok = $jumlah / $tenor;
                            $bungaPerBulan = ($jumlah * $bunga / 100) / 12;
                            $angsuran = $pokok + $bungaPerBulan;
                            $record->save();

                            // Buat tagihan angsuran pinjaman
                            $startDate = $record->tanggal_persetujuan;

                            // Hapus tagihan lama jika ada
                            \App\Models\TagihanAnggota::where('user_id', $record->user_id)
                                ->where('jenis_tagihan', 'angsuran_pinjaman')
                                ->where('pinjaman_id', $record->id)
                                ->delete();

                            for ($i = 1; $i <= $tenor; $i++) {
                                $dueDayRegular = \App\Models\KoperasiSetting::getTagihanDueDayRegular();
                                $dueDate = \Carbon\Carbon::parse($startDate)->addMonths($i)->setDay($dueDayRegular);
                                $periode = $dueDate->format('Y-m');

                                \App\Models\TagihanAnggota::create([
                                    'user_id' => $record->user_id,
                                    'jenis_tagihan' => 'angsuran_pinjaman',
                                    'jumlah' => $record->angsuran_per_bulan,
                                    'tanggal_jatuh_tempo' => $dueDate,
                                    'status' => 'belum_bayar',
                                    'pinjaman_id' => $record->id,
                                    'periode' => $periode,
                                    'keterangan' => "Angsuran pinjaman ke-{$i} dari {$tenor} bulan",
                                ]);
                            }

                            // Kirim notifikasi
                            $record->user->notify(new \App\Notifications\PinjamanStatusUpdated($record));

                            DB::commit();

                            // Tampilkan notifikasi
                            Notification::make()
                                ->title('Pinjaman Disetujui')
                                ->body("Pinjaman berhasil disetujui dan tagihan angsuran telah dibuat")
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            DB::rollBack();

                            // Log error
                            \Illuminate\Support\Facades\Log::error("Error approving loan: " . $e->getMessage());
                            Notification::make()
                                ->title('Gagal Menyetujui Pinjaman')
                                ->body("Terjadi kesalahan: " . $e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
                Tables\Actions\Action::make('reject')
                    ->label('Tolak')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->visible(fn(Pinjaman $record) => $record->status === 'pending')
                    ->form([
                        Forms\Components\Textarea::make('alasan_penolakan')
                            ->label('Alasan Penolakan')
                            ->required(),
                    ])
                    ->action(function (Pinjaman $record, array $data) {
                        $record->update([
                            'status' => 'ditolak',
                            'alasan_penolakan' => $data['alasan_penolakan'],
                            'tanggal_persetujuan' => now(),
                            'disetujui_oleh' => Auth::user()->name,
                        ]);

                        \App\Models\RiwayatTransaksi::where('referensi_id', $record->id)
                            ->where('referensi_tipe', 'App\\Models\\Pinjaman')
                            ->where('jenis_transaksi', 'pinjaman')
                            ->where('status', 'pending')
                            ->update([
                                'status' => 'ditolak',
                                'keterangan' => 'Pinjaman ditolak dengan alasan: ' . $data['alasan_penolakan']
                            ]);
                    }),

                Tables\Actions\ViewAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPinjamen::route('/'),
        ];
    }
}
