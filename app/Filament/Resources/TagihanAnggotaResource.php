<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TagihanAnggotaResource\Pages;
use App\Filament\Resources\TagihanAnggotaResource\RelationManagers;
use App\Models\TagihanAnggota;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class TagihanAnggotaResource extends Resource
{
    protected static ?string $model = TagihanAnggota::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'Tagihan Anggota';
    protected static ?string $modelLabel = 'Tagihan Anggota';
    protected static ?string $pluralModelLabel = 'Tagihan Anggota';
    protected static ?string $navigationGroup = 'Keuangan';
    protected static ?int $navigationSort = 4;
    protected static ?string $slug = 'tagihan-anggota';

    public static function canCreate(): bool
    {
        return false;
    }
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Anggota')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->label('Anggota')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\Select::make('email')
                            ->label('Email Anggota')
                            ->relationship('user', 'email')
                            ->disabled()
                            ->dehydrated(false),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Informasi Tagihan')
                    ->schema([
                        Forms\Components\Select::make('jenis_tagihan')
                            ->label('Jenis Tagihan')
                            ->options([
                                'simpanan_wajib' => 'Simpanan Wajib',
                                'angsuran_pinjaman' => 'Angsuran Pinjaman',
                            ])
                            ->required()
                            ->live(),

                        Forms\Components\TextInput::make('jumlah')
                            ->label('Jumlah Tagihan')
                            ->required()
                            ->numeric()
                            ->prefix('Rp'),

                        Forms\Components\DatePicker::make('tanggal_jatuh_tempo')
                            ->label('Tanggal Jatuh Tempo')
                            ->required()
                            ->default(now()),

                        Forms\Components\TextInput::make('periode')
                            ->label('Periode (YYYY-MM)')
                            ->hint('Format: YYYY-MM (contoh: 2023-01)')
                            ->regex('/^[0-9]{4}-[0-9]{2}$/')
                            ->placeholder('YYYY-MM'),

                        Forms\Components\Select::make('pinjaman_id')
                            ->label('Pinjaman Terkait')
                            ->relationship('pinjaman', 'id', function ($query) {
                                return $query->where('status', 'disetujui');
                            })
                            ->visible(fn(Forms\Get $get) => $get('jenis_tagihan') === 'angsuran_pinjaman')
                            ->required(fn(Forms\Get $get) => $get('jenis_tagihan') === 'angsuran_pinjaman')
                            ->formatStateUsing(fn($state, $record) => $state ?? $record->pinjaman_id ?? null)
                            ->getOptionLabelFromRecordUsing(fn($record) => "ID: {$record->id} - Rp " . number_format($record->jumlah, 0, ',', '.') . " ({$record->user->name})"),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Status Tagihan')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'belum_bayar' => 'Belum Dibayar',
                                'menunggu_verifikasi' => 'Menunggu Verifikasi',
                                'lunas' => 'Lunas',
                            ])
                            ->required()
                            ->default('belum_bayar')
                            ->live(),

                        Forms\Components\FileUpload::make('bukti_pembayaran')
                            ->label('Bukti Pembayaran')
                            ->image()
                            ->disk('public')
                            ->directory('bukti-pembayaran/tagihan')
                            ->visibility('public')
                            ->visible(fn(Forms\Get $get) => in_array($get('status'), ['menunggu_verifikasi', 'lunas']))
                            ->disabled(),

                        Forms\Components\DatePicker::make('tanggal_pembayaran')
                            ->label('Tanggal Pembayaran')
                            ->visible(fn(Forms\Get $get) => in_array($get('status'), ['menunggu_verifikasi', 'lunas'])),

                        Forms\Components\DatePicker::make('tanggal_verifikasi')
                            ->label('Tanggal Verifikasi')
                            ->visible(fn(Forms\Get $get) => $get('status') === 'lunas')
                            ->default(fn(Forms\Get $get) => $get('status') === 'lunas' ? now() : null),

                        Forms\Components\TextInput::make('diverifikasi_oleh')
                            ->label('Diverifikasi Oleh')
                            ->visible(fn(Forms\Get $get) => $get('status') === 'lunas')
                            ->default(fn(Forms\Get $get) => $get('status') === 'lunas' ? Auth::user()->name : null),

                        Forms\Components\Textarea::make('keterangan')
                            ->label('Keterangan')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
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
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('jenis_tagihan')
                    ->options([
                        'simpanan_wajib' => 'Simpanan Wajib',
                        'angsuran_pinjaman' => 'Angsuran Pinjaman',
                    ]),

                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'belum_bayar' => 'Belum Dibayar',
                        'menunggu_verifikasi' => 'Menunggu Verifikasi',
                        'lunas' => 'Lunas',
                    ]),

                Tables\Filters\Filter::make('tanggal')
                    ->form([
                        Forms\Components\DatePicker::make('tanggal_dari'),
                        Forms\Components\DatePicker::make('tanggal_sampai'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['tanggal_dari'],
                                fn(Builder $query, $date): Builder => $query->whereDate('tanggal_jatuh_tempo', '>=', $date),
                            )
                            ->when(
                                $data['tanggal_sampai'],
                                fn(Builder $query, $date): Builder => $query->whereDate('tanggal_jatuh_tempo', '<=', $date),
                            );
                    }),
                Tables\Filters\SelectFilter::make('periode')
                    ->label('Periode')
                    ->options(function () {
                        // Buat daftar bulan dari 12 bulan sebelumnya hingga 6 bulan ke depan
                        $options = [];

                        for ($i = -12; $i <= 6; $i++) {
                            $date = now()->addMonths($i)->startOfMonth();
                            $key = $date->format('Y-m');
                            $value = $date->format('F Y');
                            $options[$key] = $value;
                        }

                        return $options;
                    })
                    ->default(now()->format('Y-m'))
                    ->placeholder('Semua Periode')
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['value'],
                                fn(Builder $query, $date): Builder => $query->where('periode', $date),
                            );
                    }),
            ])

            ->actions([
                // Tables\Actions\ViewAction::make(),
                // Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('approve')
                    ->label('Verifikasi')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->visible(fn(TagihanAnggota $record): bool => $record->status === 'menunggu_verifikasi')
                    ->requiresConfirmation()
                    ->modalHeading('Verifikasi Pembayaran Tagihan')
                    ->modalDescription('Apakah Anda yakin ingin memverifikasi pembayaran tagihan ini?')
                    ->action(function (TagihanAnggota $record): void {
                        $record->update([
                            'status' => 'lunas',
                            'tanggal_verifikasi' => now(),
                            'diverifikasi_oleh' => Auth::user()->name,
                        ]);

                        // Tambahkan ke riwayat transaksi
                        if ($record->jenis_tagihan === 'simpanan_wajib') {
                            // Buat simpanan baru
                            \App\Models\Simpanan::create([
                                'user_id' => $record->user_id,
                                'jenis' => 'wajib',
                                'jumlah' => $record->jumlah,
                                'bukti_pembayaran' => $record->bukti_pembayaran,
                                'status' => 'disetujui',
                                'tanggal_pembayaran' => $record->tanggal_pembayaran,
                                'tanggal_verifikasi' => now(),
                                'diverifikasi_oleh' => Auth::user()->name,
                                'keterangan' => "Pembayaran tagihan simpanan wajib periode {$record->periode}",
                            ]);
                        }
                    }),
            ])
            ->bulkActions([])
            ->defaultSort('tanggal_jatuh_tempo');
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
            'index' => Pages\ListTagihanAnggotas::route('/'),
            // 'create' => Pages\CreateTagihanAnggota::route('/create'),
            // 'view' => Pages\ViewTagihanAnggota::route('/{record}'),
            // 'edit' => Pages\EditTagihanAnggota::route('/{record}/edit'),
        ];
    }
}
