<?php


namespace App\Filament\Resources;

use App\Filament\Resources\SHUDistributionResource\Pages;
use App\Models\SaldoKoperasi;
use App\Models\SHUBiaya;
use App\Models\SHUDistribution;
use App\Services\SHUCalculationService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class SHUDistributionResource extends Resource
{
    protected static ?string $model = SHUDistribution::class;
    protected static ?string $navigationIcon = 'heroicon-o-chart-pie';
    protected static ?string $navigationLabel = 'SHU Anggota';
    protected static ?string $pluralModelLabel = 'SHU Anggota';
    protected static ?string $modelLabel = 'SHU';
    protected static ?string $navigationGroup = 'Keuangan';
    protected static ?int $navigationSort = 30;
    protected static ?string $slug = 'shu-distribution';


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Periode & Saldo')
                    ->schema([
                        Forms\Components\Select::make('tahun')
                            ->label('Tahun Perhitungan SHU')
                            ->required()
                            ->options(
                                fn() => collect(range(date('Y') - 5, date('Y')))
                                    ->mapWithKeys(fn($year) => [$year => $year])
                            )
                            ->default(date('Y') - 1),

                        Forms\Components\TextInput::make('saldo_koperasi')
                            ->label('Saldo Koperasi')
                            ->disabled()
                            ->dehydrated()
                            ->prefix('Rp'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Komponen Biaya')
                    ->schema([
                        Forms\Components\TextInput::make('biaya_operasional')
                            ->label('Biaya Operasional')
                            ->required()
                            ->numeric()
                            ->prefix('Rp')
                            ->stripCharacters(',')
                            ->mask(RawJs::make('$money($input)')),

                        Forms\Components\TextInput::make('pajak')
                            ->label('Pajak')
                            ->required()
                            ->numeric()
                            ->mask(RawJs::make('$money($input)'))
                            ->stripCharacters(',')
                            ->prefix('Rp'),

                        Forms\Components\TextInput::make('biaya_lainnya')
                            ->label('Biaya Lainnya')
                            ->numeric()
                            ->stripCharacters(',')
                            ->mask(RawJs::make('$money($input)'))
                            ->prefix('Rp'),

                        Forms\Components\Textarea::make('keterangan_biaya')
                            ->label('Keterangan Biaya Lainnya')
                            ->placeholder('Masukkan keterangan biaya lainnya jika ada')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Distribusi SHU')
                    ->schema([
                        Forms\Components\TextInput::make('persentase_dana_cadangan')
                            ->label('Persentase Dana Cadangan (%)')
                            ->required()
                            ->numeric()
                            ->default(40)
                            ->minValue(0)
                            ->maxValue(100)
                            ->suffix('%')
                            ->afterStateUpdated(function (Forms\Set $set, Forms\Get $get) {
                                SHUDistributionResource::hitungTotalBiayaDanSHU($get, $set);
                            })
                            ->live(),

                        Forms\Components\TextInput::make('persentase_jasa_usaha')
                            ->label('Persentase Jasa Usaha (%)')
                            ->required()
                            ->numeric()
                            ->default(20)
                            ->minValue(0)
                            ->maxValue(100)
                            ->suffix('%')
                            ->afterStateUpdated(function (Forms\Set $set, Forms\Get $get) {
                                SHUDistributionResource::hitungTotalBiayaDanSHU($get, $set);
                            })
                            ->live(),

                        Forms\Components\TextInput::make('persentase_jasa_modal')
                            ->label('Persentase Jasa Modal (%)')
                            ->required()
                            ->numeric()
                            ->default(20)
                            ->minValue(0)
                            ->maxValue(100)
                            ->suffix('%')
                            ->afterStateUpdated(function (Forms\Set $set, Forms\Get $get) {
                                SHUDistributionResource::hitungTotalBiayaDanSHU($get, $set);
                            })
                            ->live(),

                        Forms\Components\TextInput::make('persentase_jasa_pinjaman')
                            ->label('Persentase Jasa Pinjaman (%)')
                            ->required()
                            ->numeric()
                            ->default(20)
                            ->minValue(0)
                            ->maxValue(100)
                            ->suffix('%')
                            ->afterStateUpdated(function (Forms\Set $set, Forms\Get $get) {
                                SHUDistributionResource::hitungTotalBiayaDanSHU($get, $set);
                            })
                            ->live(),

                        Forms\Components\TextInput::make('total_persentase')
                            ->label('Total Persentase')
                            ->disabled()
                            ->dehydrated(false)
                            ->default(100)
                            ->suffix('%')
                            ->reactive()
                            ->afterStateHydrated(function (Forms\Components\TextInput $component, Forms\Get $get) {
                                $total =
                                    floatval($get('persentase_dana_cadangan') ?? 40) +
                                    floatval($get('persentase_jasa_usaha') ?? 20) +
                                    floatval($get('persentase_jasa_modal') ?? 20) +
                                    floatval($get('persentase_jasa_pinjaman') ?? 20);
                                $component->state($total);
                            }),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Hasil Perhitungan')
                    ->schema([
                        Forms\Components\TextInput::make('total_biaya')
                            ->label('Total Biaya')
                            ->disabled()
                            ->dehydrated()
                            ->prefix('Rp')
                            ->stripCharacters(',')
                            ->mask(RawJs::make('$money($input)'))
                            ->default(0),

                        Forms\Components\TextInput::make('total_shu')
                            ->label('Total SHU untuk Dibagikan')
                            ->disabled()
                            ->dehydrated()
                            ->prefix('Rp')
                            ->stripCharacters(',')
                            ->mask(RawJs::make('$money($input)'))
                            ->default(0),

                        Forms\Components\TextInput::make('dana_cadangan')
                            ->label('Dana Cadangan')
                            ->disabled()
                            ->dehydrated()
                            ->prefix('Rp')
                            ->default(0),
                    ])
                    ->columns(2),

                Forms\Components\Hidden::make('show_simulasi')
                    ->default(false),
                Forms\Components\Hidden::make('jumlah_anggota'),
                Forms\Components\Hidden::make('avg_simpanan'),
                Forms\Components\Hidden::make('estimasi_distribusi_rata'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Anggota')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('tahun')
                    ->label('Tahun')
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_simpanan')
                    ->label('Total Simpanan')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_biaya_admin')
                    ->label('Biaya Admin')
                    ->money('IDR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_bunga_pinjaman')
                    ->label('Bunga Pinjaman')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('persentase_kontribusi')
                    ->label('Persentase')
                    ->formatStateUsing(fn($state) => number_format($state * 100, 2) . '%')
                    ->sortable(),

                Tables\Columns\TextColumn::make('jumlah_shu')
                    ->label('Jumlah SHU')
                    ->money('IDR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'pending' => 'warning',
                        'dibagikan' => 'success',
                        'ditolak' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('tanggal_distribusi')
                    ->label('Tanggal Distribusi')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('tahun')
                    ->label('Tahun')
                    ->options(function () {
                        return collect((new SHUCalculationService)->getDistributionYears())
                            ->mapWithKeys(fn($tahun) => [$tahun => $tahun]);
                    }),

                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Pending',
                        'dibagikan' => 'Dibagikan',
                        'ditolak' => 'Ditolak',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Hitung SHU Baru'),


                Tables\Actions\Action::make('distribusiSHU')
                    ->label('Distribusikan SHU')
                    ->icon('heroicon-o-banknotes')
                    ->color('success')
                    ->form([
                        Forms\Components\Select::make('tahun')
                            ->label('Tahun SHU')
                            ->required()
                            ->options(function () {
                                $years = SHUDistribution::where('status', 'pending')
                                    ->select('tahun')
                                    ->distinct()
                                    ->orderBy('tahun', 'desc')
                                    ->pluck('tahun')
                                    ->toArray();

                                return collect($years)->mapWithKeys(fn($year) => [$year => $year]);
                            }),
                    ])
                    ->action(function (array $data) {
                        $tahun = (int) $data['tahun'];

                        $result = (new SHUCalculationService)->distributeSHU($tahun);

                        if ($result['success']) {
                            $shuBiaya = SHUBiaya::where('tahun', $tahun)->first();
                            if ($shuBiaya && $shuBiaya->dana_cadangan) {
                                SaldoKoperasi::query()->update(['saldo' => $shuBiaya->dana_cadangan]);
                            }

                            Notification::make()
                                ->title('SHU berhasil didistribusikan')
                                ->body($result['message'])
                                ->success()
                                ->send();

                            return redirect(SHUDistributionResource::getUrl('index'));
                        } else {
                            Notification::make()
                                ->title('Distribusi SHU gagal')
                                ->body($result['message'])
                                ->danger()
                                ->send();
                        }
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Distribusikan SHU')
                    ->modalDescription('Apakah Anda yakin ingin mendistribusikan SHU? Tindakan ini akan mengubah status SHU menjadi "dibagikan" dan mencatat transaksi distribusi SHU ke anggota.'),

                Tables\Actions\Action::make('batalkanSHU')
                    ->label('Batalkan Perhitungan')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->form([
                        Forms\Components\Select::make('tahun')
                            ->label('Tahun SHU')
                            ->required()
                            ->options(function () {
                                $years = SHUDistribution::where('status', 'pending')
                                    ->select('tahun')
                                    ->distinct()
                                    ->orderBy('tahun', 'desc')
                                    ->pluck('tahun')
                                    ->toArray();

                                return collect($years)->mapWithKeys(fn($year) => [$year => $year]);
                            }),
                    ])
                    ->action(function (array $data) {
                        $tahun = (int) $data['tahun'];

                        $result = (new SHUCalculationService)->cancelSHUCalculation($tahun);

                        if ($result) {
                            Notification::make()
                                ->title('Perhitungan SHU dibatalkan')
                                ->body("Perhitungan SHU untuk tahun $tahun berhasil dibatalkan")
                                ->success()
                                ->send();

                            return redirect(SHUDistributionResource::getUrl('index'));
                        } else {
                            Notification::make()
                                ->title('Pembatalan gagal')
                                ->body("Gagal membatalkan perhitungan SHU tahun $tahun")
                                ->danger()
                                ->send();
                        }
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Batalkan Perhitungan SHU')
                    ->modalDescription('Apakah Anda yakin ingin membatalkan perhitungan SHU? Tindakan ini akan menghapus semua data perhitungan SHU yang belum didistribusikan (status pending).'),
            ])
            ->bulkActions([])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSHUDistributions::route('/'),
            'create' => Pages\CreateSHUDistribution::route('/create'),
            'view' => Pages\ViewSHUDistribution::route('/{record}'),
        ];
    }

    public static function canCreate(): bool
    {
        return true;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function hitungTotalBiayaDanSHU(Forms\Get $get, Forms\Set $set): void
    {
        try {

            $saldoKoperasi = (int) SaldoKoperasi::getSaldo();

            $biayaOperasional = (float) ($get('biaya_operasional') ?? 0);
            $pajak = (float) ($get('pajak') ?? 0);
            $danaCadangan = (float) ($get('dana_cadangan') ?? 0);
            $biayaLainnya = (float) ($get('biaya_lainnya') ?? 0);

            $totalBiaya = $biayaOperasional + $pajak + $danaCadangan + $biayaLainnya;
            $totalSHU = max(0, $saldoKoperasi - $totalBiaya);

            $set('total_biaya', $totalBiaya);
            $set('total_shu', $totalSHU);
        } catch (\Exception $e) {
            Log::error('Error in hitungTotalBiayaDanSHU: ' . $e->getMessage());

            $set('total_biaya', 0);
            $set('total_shu', 0);
        }
    }
}
