<?php

namespace App\Filament\Anggota\Resources;

use App\Filament\Anggota\Resources\TagihanSayaResource\Pages;
use App\Filament\Anggota\Resources\TagihanSayaResource\RelationManagers;
use App\Models\TagihanAnggota;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class TagihanSayaResource extends Resource
{
    protected static ?string $model = TagihanAnggota::class;

    protected static ?string $navigationIcon = 'heroicon-o-receipt-percent';
    protected static ?string $navigationLabel = 'Tagihan Saya';
    protected static ?string $navigationGroup = 'Keuangan';
    protected static ?int $navigationSort = 3;
    protected static ?string $slug = 'tagihan-saya';

    public static function getNavigationBadge(): ?string
    {
        $userId = Auth::id();

        $belumBayar = TagihanAnggota::where('user_id', $userId)
            ->where('status', 'belum_bayar')
            ->count();

        $menungguVerifikasi = TagihanAnggota::where('user_id', $userId)
            ->where('status', 'menunggu_verifikasi')
            ->count();

        if ($belumBayar === 0 && $menungguVerifikasi === 0) {
            return null;
        }

        return "{$belumBayar} 🔴 | {$menungguVerifikasi} 🟡";
    }

    public static function getNavigationBadgeColor(): ?string
    {
        $userId = Auth::id();

        if (TagihanAnggota::where('user_id', $userId)->where('status', 'belum_bayar')->exists()) {
            return 'danger';
        }

        if (TagihanAnggota::where('user_id', $userId)->where('status', 'menunggu_verifikasi')->exists()) {
            return 'warning';
        }
        return 'success';
    }

    public static function canAccess(): bool
    {
        return Auth::user()->hasRole('anggota_tetap');
    }
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Detail Tagihan')
                    ->schema([
                        Forms\Components\TextInput::make('jenis_tagihan_formatted')
                            ->label('Jenis Tagihan')
                            ->formatStateUsing(function ($state, TagihanAnggota $record) {
                                return match ($record->jenis_tagihan) {
                                    'simpanan_wajib' => 'Simpanan Wajib',
                                    'angsuran_pinjaman' => 'Angsuran Pinjaman',
                                    default => ucfirst(str_replace('_', ' ', $record->jenis_tagihan))
                                };
                            })
                            ->disabled(),

                        Forms\Components\TextInput::make('jumlah')
                            ->label('Jumlah Tagihan')
                            ->prefix('Rp')
                            ->disabled(),

                        Forms\Components\DatePicker::make('tanggal_jatuh_tempo')
                            ->label('Jatuh Tempo')
                            ->displayFormat('d M Y')
                            ->disabled(),

                        Forms\Components\TextInput::make('periode_formatted')
                            ->label('Periode')
                            ->formatStateUsing(function ($state, TagihanAnggota $record) {
                                if ($record->periode) {
                                    list($year, $month) = explode('-', $record->periode);
                                    return date('F Y', mktime(0, 0, 0, $month, 1, $year));
                                }
                                return '-';
                            })
                            ->disabled()
                            ->visible(fn(TagihanAnggota $record) => $record->periode !== null),

                        Forms\Components\TextInput::make('status_formatted')
                            ->label('Status')
                            ->formatStateUsing(function ($state, TagihanAnggota $record) {
                                return match ($record->status) {
                                    'belum_bayar' => 'Belum Dibayar',
                                    'menunggu_verifikasi' => 'Menunggu Verifikasi',
                                    'lunas' => 'Lunas',
                                    default => ucfirst(str_replace('_', ' ', $record->status))
                                };
                            })
                            ->disabled(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Upload Bukti Pembayaran')
                    ->schema([
                        Forms\Components\FileUpload::make('bukti_pembayaran')
                            ->label('Bukti Pembayaran')
                            ->required()
                            ->image()
                            ->maxSize(2048) // 2MB
                            ->directory('bukti-pembayaran/tagihan')
                            ->disk('public')
                            ->visibility('public')
                            ->columnSpanFull()
                            ->getUploadedFileNameForStorageUsing(
                                fn(TemporaryUploadedFile $file): string =>
                                'tagihan-' . Auth::id() . '-' . time() . '.' . $file->getClientOriginalExtension()
                            )
                            ->disabledOn('view'),

                        Forms\Components\Textarea::make('keterangan')
                            ->label('Keterangan')
                            ->placeholder('Tambahkan keterangan jika diperlukan')
                            ->columnSpanFull()
                            ->disabledOn('view'),
                    ])
                    ->visible(fn(TagihanAnggota $record) => $record->status === 'belum_bayar'),

                Forms\Components\Section::make('Informasi Pembayaran')
                    ->schema([
                        Forms\Components\DatePicker::make('tanggal_pembayaran')
                            ->label('Tanggal Pembayaran')
                            ->displayFormat('d M Y')
                            ->disabled(),

                        Forms\Components\TextInput::make('diverifikasi_oleh')
                            ->label('Diverifikasi Oleh')
                            ->disabled(),

                        Forms\Components\DatePicker::make('tanggal_verifikasi')
                            ->label('Tanggal Verifikasi')
                            ->displayFormat('d M Y')
                            ->disabled(),

                        Forms\Components\ViewField::make('bukti_pembayaran_preview')
                            ->label('Bukti Pembayaran')
                            ->view('components.view-bukti-tagihan'),
                    ])
                    ->columns(2)
                    ->visible(fn(TagihanAnggota $record) => in_array($record->status, ['menunggu_verifikasi', 'lunas'])),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal Dibuat')
                    ->date('d M Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('jenis_tagihan')
                    ->label('Jenis Tagihan')
                    ->formatStateUsing(function ($state) {
                        return match ($state) {
                            'simpanan_wajib' => 'Simpanan Wajib',
                            'angsuran_pinjaman' => 'Angsuran Pinjaman',
                            default => ucfirst(str_replace('_', ' ', $state))
                        };
                    })
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('periode')
                    ->label('Periode')
                    ->formatStateUsing(function ($state) {
                        if ($state) {
                            list($year, $month) = explode('-', $state);
                            return date('F Y', mktime(0, 0, 0, $month, 1, $year));
                        }
                        return '-';
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('jumlah')
                    ->label('Jumlah')
                    ->money('IDR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('tanggal_jatuh_tempo')
                    ->label('Jatuh Tempo')
                    ->date('d M Y')
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->formatStateUsing(function ($state) {
                        return match ($state) {
                            'belum_bayar' => 'Belum Dibayar',
                            'menunggu_verifikasi' => 'Menunggu Verifikasi',
                            'lunas' => 'Lunas',
                            default => ucfirst(str_replace('_', ' ', $state))
                        };
                    })
                    ->colors([
                        'danger' => 'belum_bayar',
                        'warning' => 'menunggu_verifikasi',
                        'success' => 'lunas',
                    ]),

                Tables\Columns\TextColumn::make('tanggal_pembayaran')
                    ->label('Tanggal Bayar')
                    ->date('d M Y')
                    ->sortable(),
            ])
            ->filters([
                // Filter jenis tagihan
                Tables\Filters\SelectFilter::make('jenis_tagihan')
                    ->options([
                        'simpanan_wajib' => 'Simpanan Wajib',
                        'angsuran_pinjaman' => 'Angsuran Pinjaman',
                    ])
                    ->label('Jenis Tagihan'),

                // Filter status
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'belum_bayar' => 'Belum Dibayar',
                        'menunggu_verifikasi' => 'Menunggu Verifikasi',
                        'lunas' => 'Lunas',
                    ])
                    ->label('Status'),

                // Filter periode yang diperbaiki
                Tables\Filters\SelectFilter::make('periode')
                    ->label('Periode')
                    ->options(function () {
                        // Buat daftar periode dari tagihan yang ada di database
                        // untuk memastikan semua periode yang ada di data ditampilkan
                        $periodes = TagihanAnggota::where('user_id', Auth::id())
                            ->select('periode')
                            ->distinct()
                            ->whereNotNull('periode')
                            ->pluck('periode')
                            ->toArray();

                        // Tambahkan periode dari 3 bulan lalu hingga 3 bulan ke depan untuk melengkapi
                        for ($i = -3; $i <= 3; $i++) {
                            $period = now()->addMonths($i)->format('Y-m');
                            if (!in_array($period, $periodes)) {
                                $periodes[] = $period;
                            }
                        }

                        // Urutkan periode
                        sort($periodes);

                        // Format untuk tampilan
                        $options = [];
                        foreach ($periodes as $periode) {
                            $date = Carbon::createFromFormat('Y-m', $periode)->format('F Y');
                            $options[$periode] = $date;
                        }

                        return $options;
                    })
                    ->default(now()->format('Y-m'))
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['value'],
                            fn(Builder $query, $date): Builder => $query->where('periode', $date)
                        );
                    }),
            ])
            ->actions([
                // Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('bayar')
                    ->label('Bayar')
                    ->icon('heroicon-o-currency-dollar')
                    ->color('success')
                    ->action(function (TagihanAnggota $record): void {
                        redirect()->to(TagihanSayaResource::getUrl('edit', ['record' => $record]));
                    })
                    ->visible(fn(TagihanAnggota $record): bool => $record->status === 'belum_bayar'),
            ])
            ->bulkActions([])
            ->defaultSort('tanggal_jatuh_tempo')

            ->modifyQueryUsing(function (Builder $query) {
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
            'index' => Pages\ListTagihanSayas::route('/'),
            'edit' => Pages\EditTagihanSaya::route('/{record}/edit'),
            // 'view' => Pages\ViewTagihanSaya::route('/{record}'),
        ];
    }

    public static function getModelLabel(): string
    {
        return 'Tagihan';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Tagihan Saya';
    }
}
