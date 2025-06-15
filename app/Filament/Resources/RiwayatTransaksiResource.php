<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RiwayatTransaksiResource\Pages;
use App\Filament\Resources\RiwayatTransaksiResource\RelationManagers;
use App\Models\RiwayatTransaksi;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class RiwayatTransaksiResource extends Resource
{
    protected static ?string $model = RiwayatTransaksi::class;
    protected static ?string $navigationIcon = 'heroicon-o-clock';
    protected static ?string $navigationLabel = 'Riwayat Transaksi';
    protected static ?string $modelLabel = 'Riwayat Transaksi';
    protected static ?string $pluralModelLabel = 'Riwayat Transaksi';
    protected static ?string $navigationGroup = 'Keuangan';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Transaksi')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->label('Anggota')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\Select::make('jenis_transaksi')
                            ->label('Jenis Transaksi')
                            ->options([
                                'simpanan_pokok' => 'Simpanan Pokok',
                                'simpanan_wajib' => 'Simpanan Wajib',
                                'simpanan_sukarela' => 'Simpanan Sukarela',
                                'pinjaman' => 'Pengajuan Pinjaman',
                                'pembayaran_pinjaman' => 'Pembayaran Pinjaman',
                            ])
                            ->required(),

                        Forms\Components\TextInput::make('jumlah')
                            ->label('Jumlah')
                            ->required()
                            ->numeric()
                            ->prefix('Rp'),

                        Forms\Components\DatePicker::make('tanggal')
                            ->label('Tanggal')
                            ->required(),

                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'pending' => 'Pending',
                                'disetujui' => 'Disetujui',
                                'ditolak' => 'Ditolak',
                            ])
                            ->required(),

                        Forms\Components\Textarea::make('keterangan')
                            ->label('Keterangan')
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
                    ->label('Nama Anggota')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('tanggal')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('jenis_transaksi')
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

                Tables\Columns\TextColumn::make('jumlah')
                    ->label('Jumlah')
                    ->money('IDR')
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'disetujui',
                        'danger' => 'ditolak',
                    ]),

                Tables\Columns\TextColumn::make('keterangan')
                    ->label('Keterangan')
                    ->limit(30)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 30) {
                            return null;
                        }
                        return $state;
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('user_id')
                    ->label('Anggota')
                    ->options(fn() => User::pluck('name', 'id')->toArray())
                    ->searchable(),

                Tables\Filters\SelectFilter::make('jenis_transaksi')
                    ->label('Jenis Transaksi')
                    ->options([
                        'simpanan_pokok' => 'Simpanan Pokok',
                        'simpanan_wajib' => 'Simpanan Wajib',
                        'simpanan_sukarela' => 'Simpanan Sukarela',
                        'pinjaman' => 'Pengajuan Pinjaman',
                        'pembayaran_pinjaman' => 'Pembayaran Pinjaman',
                    ]),

                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'disetujui' => 'Disetujui',
                        'ditolak' => 'Ditolak',
                    ]),

                Tables\Filters\Filter::make('tanggal')
                    ->form([
                        Forms\Components\DatePicker::make('dari_tanggal'),
                        Forms\Components\DatePicker::make('sampai_tanggal'),
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
            ->actions([
                // Tables\Actions\ViewAction::make(),
                // Tables\Actions\EditAction::make(),
                // Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ])
            ->defaultSort('tanggal', 'desc');
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
            'index' => Pages\ListRiwayatTransaksis::route('/'),
            // 'create' => Pages\CreateRiwayatTransaksi::route('/create'),
            // 'edit' => Pages\EditRiwayatTransaksi::route('/{record}/edit'),
        ];
    }
}
