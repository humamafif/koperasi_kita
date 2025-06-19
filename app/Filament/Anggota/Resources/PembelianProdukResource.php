<?php

namespace App\Filament\Anggota\Resources;

use App\Filament\Anggota\Resources\PembelianProdukResource\Pages;
use App\Filament\Anggota\Resources\PembelianProdukResource\RelationManagers;
use App\Models\KoperasiSetting;
use App\Models\PembelianProduk;
use App\Models\SaldoKoperasi;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class PembelianProdukResource extends Resource
{
    protected static ?string $model = PembelianProduk::class;
    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';
    protected static ?string $navigationLabel = 'Penjualan Produk';
    protected static ?string $navigationGroup = 'Produk';
    protected static ?int $navigationSort = 2;
    protected static ?string $pluralModelLabel = 'Daftar Penjualan Produk';
    protected static ?string $modelLabel = 'Penjualan Produk';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Detail Penjualan')
                    ->schema([
                        Forms\Components\TextInput::make('nama')
                            ->label('Nama Produk')
                            ->disabled()
                            ->afterStateHydrated(function ($component, $state, $record) {
                                if ($record && $record->produk) {
                                    $component->state($record->produk->nama);
                                }
                            }),
                        Forms\Components\TextInput::make('name')
                            ->label('Pembeli')
                            ->disabled()
                            ->afterStateHydrated(function ($component, $state, $record) {
                                if ($record && $record->pembeli) {
                                    $component->state($record->pembeli->name);
                                }
                            }),

                        Forms\Components\TextInput::make('jumlah')
                            ->label('Jumlah')
                            ->disabled(),

                        Forms\Components\TextInput::make('harga_satuan')
                            ->label('Harga Satuan')
                            ->prefix('Rp')
                            ->disabled(),

                        Forms\Components\TextInput::make('total')
                            ->label('Total')
                            ->prefix('Rp')
                            ->disabled(),

                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'pending' => 'Menunggu',
                                'diproses' => 'Diproses',
                                'dikirim' => 'Dikirim',
                                'selesai' => 'Selesai',
                                'dibatalkan' => 'Dibatalkan',
                            ])
                            ->disabled(fn($record) => in_array($record?->status, ['selesai', 'dibatalkan'])),

                        Forms\Components\TextInput::make('biaya_admin')
                            ->label('Biaya Admin (' . KoperasiSetting::getBiayaAdminPercentDisplay() . ')')
                            ->prefix('Rp')
                            ->disabled(),

                        Forms\Components\TextInput::make('total_penjual')
                            ->label('Diterima Penjual')
                            ->prefix('Rp')
                            ->disabled(),

                        Forms\Components\DateTimePicker::make('created_at')
                            ->label('Tanggal Pembelian')
                            ->displayFormat('d M Y H:i')
                            ->disabled(),

                        Forms\Components\Textarea::make('catatan')
                            ->label('Catatan')
                            ->disabled()
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
            ]);
    }

    public static function table(Table $table): Table
    {

        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('produk.gambar')
                    ->label('Gambar')
                    ->disk('public')
                    ->square()
                    ->size(70),

                Tables\Columns\TextColumn::make('produk.nama')
                    ->label('Nama Produk')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('pembeli.name')
                    ->label('Pembeli')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('jumlah')
                    ->label('Jumlah')
                    ->sortable(),

                Tables\Columns\TextColumn::make('total')
                    ->label('Total')
                    ->money('IDR')
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'danger' => 'dibatalkan',
                        'warning' => 'pending',
                        'primary' => 'diproses',
                        'info' => 'dikirim',
                        'success' => 'selesai',
                    ]),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('biaya_admin')
                    ->label('Biaya Admin (' . KoperasiSetting::getBiayaAdminPercentDisplay() . ')')
                    ->money('IDR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_penjual')
                    ->label('Diterima Penjual')
                    ->money('IDR')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Menunggu',
                        'diproses' => 'Diproses',
                        'dikirim' => 'Dikirim',
                        'selesai' => 'Selesai',
                        'dibatalkan' => 'Dibatalkan',
                    ]),
            ])
            ->actions([
                // Tables\Actions\ViewAction::make(),
                // Tables\Actions\EditAction::make()
                //     ->visible(
                //         fn(PembelianProduk $record): bool =>
                //         $record->penjual_id === Auth::id() &&
                //             !in_array($record->status, ['selesai', 'dibatalkan'])
                //     ),
                Tables\Actions\Action::make('updateStatus')
                    ->label('Update Status')
                    ->icon('heroicon-o-arrow-path')
                    ->form([
                        Forms\Components\Select::make('status')
                            ->label('Status Baru')
                            ->options([
                                'pending' => 'Menunggu',
                                'diproses' => 'Diproses',
                                'dikirim' => 'Dikirim',
                                'selesai' => 'Selesai',
                                'dibatalkan' => 'Dibatalkan',
                            ])
                            ->required(),
                    ])
                    ->action(function (PembelianProduk $record, array $data): void {
                        $oldStatus = $record->status;
                        $record->status = $data['status'];
                        $record->save();

                        // Jika dibatalkan, kembalikan stok
                        if ($data['status'] === 'dibatalkan' && $oldStatus !== 'dibatalkan') {
                            $record->produk->stok += $record->jumlah;
                            $record->produk->save();
                        }

                        if ($data['status'] === 'selesai' && $oldStatus !== 'selesai') {
                            $biayaAdmin = $record->biaya_admin;
                            SaldoKoperasi::tambah($biayaAdmin);
                        }
                    })
                    ->visible(
                        fn(PembelianProduk $record): bool =>
                        $record->penjual_id === Auth::id() &&
                            !in_array($record->status, ['selesai', 'dibatalkan'])
                    ),
            ])
            ->bulkActions([])
            ->defaultSort('created_at', 'desc');
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
            'index' => Pages\ListPembelianProduks::route('/'),
            // 'view' => Pages\ViewPembelianProduk::route('/{record}'),
            // 'edit' => Pages\EditPembelianProduk::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        // Hanya menampilkan pembelian produk yang user sebagai penjual
        return parent::getEloquentQuery()->where('penjual_id', Auth::id());
    }
}
