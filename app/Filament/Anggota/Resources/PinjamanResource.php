<?php

namespace App\Filament\Anggota\Resources;

use App\Filament\Anggota\Resources\PinjamanResource\Pages;
use App\Filament\Anggota\Resources\PinjamanResource\RelationManagers;
use App\Models\BungaPinjaman;
use App\Models\Pinjaman;
use App\Models\TenorPinjaman;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class PinjamanResource extends Resource
{
    protected static ?string $model = Pinjaman::class;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel = 'Pengajuan Pinjaman';
    protected static ?string $pluralModelLabel = 'pinjaman';
    protected static ?string $slug = 'pinjaman';

    public static function canAccess(): bool
    {
        return Auth::user()->hasRole('anggota_tetap');
    }

    public static function canCreate(): bool
    {
        return Auth::user()->can('create_loan');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Pengajuan Pinjaman')
                    ->schema([
                        Forms\Components\TextInput::make('jumlah')
                            ->label('Jumlah Pinjaman')
                            ->required()
                            ->numeric()
                            ->prefix('Rp')
                            ->minValue(1000000)
                            ->maxValue(50000000)
                            ->hint('Minimal Rp 1.000.000, Maksimal Rp 50.000.000')
                            ->live(),

                        Forms\Components\Select::make('tenor_id')
                            ->label('Jangka Waktu')
                            ->required()
                            ->options(function () {
                                return TenorPinjaman::where('aktif', true)
                                    ->orderBy('durasi')
                                    ->get()
                                    ->mapWithKeys(function ($tenor) {
                                        return [$tenor->id => $tenor->nama . ' - Margin ' . $tenor->bunga . '%'];
                                    })
                                    ->toArray();
                            })
                            ->afterStateUpdated(function (Forms\Set $set, Forms\Get $get, $state) {
                                if ($state) {
                                    $set('showSimulasi', true);

                                    // Hitung simulasi otomatis ketika tenor berubah
                                    $jumlah = $get('jumlah') ?: 0;
                                    $tenor = TenorPinjaman::find($state);

                                    if ($tenor && $jumlah > 0) {
                                        $durasi = $tenor->durasi;
                                        $bunga = $tenor->bunga;

                                        // Hitung angsuran
                                        $pokok = $jumlah / $durasi;
                                        $bungaPerBulan = ($jumlah * $bunga / 100) / $durasi;
                                        $angsuran = $pokok + $bungaPerBulan;

                                        // Hitung total
                                        $totalBunga = $jumlah * $bunga / 100 * ($durasi / 12);
                                        $totalBayar = $jumlah + $totalBunga;

                                        $set('angsuran_bulanan', 'Rp ' . number_format($angsuran, 0, ',', '.'));
                                        $set('total_bayar', 'Rp ' . number_format($totalBayar, 0, ',', '.'));
                                        $set('info_bunga', 'Margin: ' . $bunga . '% per tahun');
                                    }
                                }
                            })
                            ->live(),

                        Forms\Components\Select::make('tujuan')
                            ->label('Tujuan Pinjaman')
                            ->required()
                            ->options([
                                'Modal Usaha' => 'Modal Usaha',
                                'Pendidikan' => 'Pendidikan',
                                'Kesehatan' => 'Kesehatan',
                                'Renovasi Rumah' => 'Renovasi Rumah',
                                'Lainnya' => 'Lainnya',
                            ]),

                        Forms\Components\Textarea::make('keterangan')
                            ->label('Keterangan Tambahan')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Simulasi Pinjaman')
                    ->schema([
                        Forms\Components\Hidden::make('showSimulasi')
                            ->default(false),

                        Forms\Components\Placeholder::make('info_bunga')
                            ->label('Informasi Bunga')
                            ->content(fn(Forms\Get $get): string => $get('info_bunga') ?: 'Pilih tenor untuk melihat margin')
                            ->visible(fn(Forms\Get $get): bool => $get('showSimulasi'))
                            ->columnSpanFull(),

                        Forms\Components\Placeholder::make('angsuran_bulanan')
                            ->label('Angsuran per Bulan')
                            ->content(fn(Forms\Get $get): string => $get('angsuran_bulanan') ?: 'Pilih tenor dan masukkan jumlah')
                            ->visible(fn(Forms\Get $get): bool => $get('showSimulasi'))
                            ->extraAttributes(['class' => 'text-lg font-bold']),

                        Forms\Components\Placeholder::make('total_bayar')
                            ->label('Total Pembayaran')
                            ->content(fn(Forms\Get $get): string => $get('total_bayar') ?: 'Pilih tenor dan masukkan jumlah')
                            ->visible(fn(Forms\Get $get): bool => $get('showSimulasi'))
                            ->extraAttributes(['class' => 'text-lg font-bold']),

                        Forms\Components\Actions::make([
                            Forms\Components\Actions\Action::make('hitungUlang')
                                ->label('Hitung Ulang Simulasi')
                                ->icon('heroicon-o-calculator')
                                ->color('primary')
                                ->visible(fn(Forms\Get $get): bool => $get('showSimulasi'))
                                ->action(function (Forms\Set $set, Forms\Get $get) {
                                    $jumlah = $get('jumlah') ?: 0;
                                    $tenorId = $get('tenor_id');
                                    $tenor = TenorPinjaman::find($tenorId);

                                    if ($tenor && $jumlah > 0) {
                                        $durasi = $tenor->durasi;
                                        $bunga = $tenor->bunga;

                                        // Hitung angsuran
                                        $pokok = $jumlah / $durasi;
                                        $bungaPerBulan = ($jumlah * $bunga / 100) / $durasi;
                                        $angsuran = $pokok + $bungaPerBulan;

                                        // Hitung total
                                        $totalBunga = $jumlah * $bunga / 100 * ($durasi / 12);
                                        $totalBayar = $jumlah + $totalBunga;

                                        $set('angsuran_bulanan', 'Rp ' . number_format($angsuran, 0, ',', '.'));
                                        $set('total_bayar', 'Rp ' . number_format($totalBayar, 0, ',', '.'));
                                        $set('info_bunga', 'Margin: ' . $bunga . '% per tahun');
                                    }
                                })
                                ->size('sm'),
                        ])->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
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
                    ->description(fn(Pinjaman $record): string => 'Margin: ' . $record->nilai_bunga . '%')
                    ->sortable(),

                Tables\Columns\TextColumn::make('angsuran_per_bulan')
                    ->label('Angsuran/Bulan')
                    ->money('IDR'),

                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'disetujui',
                        'danger' => 'ditolak',
                    ]),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->modifyQueryUsing(function (Builder $query) {
                // Hanya tampilkan pinjaman milik anggota yang login
                return $query->where('user_id', Auth::id());
            });
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
            'index' => Pages\ListPinjamans::route('/'),
            'create' => Pages\CreatePinjaman::route('/create'),
            'edit' => Pages\EditPinjaman::route('/{record}/edit'),
            'view' => Pages\ViewPinjaman::route('/{record}'),
        ];
    }
}
