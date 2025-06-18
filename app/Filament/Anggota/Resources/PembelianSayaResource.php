<?php

namespace App\Filament\Anggota\Resources;

use App\Filament\Anggota\Resources\PembelianSayaResource\Pages;
use App\Models\PembelianProduk;
use App\Models\SaldoKoperasi;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class PembelianSayaResource extends Resource
{
    protected static ?string $model = PembelianProduk::class;
    protected static ?string $navigationIcon = 'heroicon-o-receipt-percent';
    protected static ?string $navigationLabel = 'Pembelian Saya';
    protected static ?string $navigationGroup = 'Produk';
    protected static ?int $navigationSort = 3;
    protected static ?string $pluralModelLabel = 'Pembelian Saya';
    protected static ?string $modelLabel = 'Pembelian';
    protected static ?string $slug = 'pembelian-saya';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Detail Pembelian')
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
                            ->label('Nama Penjual')
                            ->disabled()
                            ->afterStateHydrated(function ($component, $state, $record) {
                                if ($record && $record->penjual) {
                                    $component->state($record->penjual->name);
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
                            ->label('Total Harga')
                            ->prefix('Rp')
                            ->disabled(),

                        Forms\Components\TextInput::make('status')
                            ->label('Status')
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

                Tables\Columns\TextColumn::make('penjual.name')
                    ->label('Penjual')
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
                    ->label('Tanggal Pembelian')
                    ->dateTime('d M Y H:i')
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
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('terima')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function (PembelianProduk $record) {
                        $record->status = 'selesai';
                        $record->save();

                        if ($record['status'] === 'selesai') {
                            $biayaAdmin = $record->biaya_admin;
                            SaldoKoperasi::tambah($biayaAdmin);
                        }
                    })
                    ->visible(fn(PembelianProduk $record) => $record->status === 'dikirim'),

                Tables\Actions\Action::make('batalkan')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function (PembelianProduk $record) {
                        $record->status = 'dibatalkan';
                        $record->save();

                        // Kembalikan stok produk
                        $record->produk->stok += $record->jumlah;
                        $record->produk->save();
                    })
                    ->visible(fn(PembelianProduk $record) => $record->status === 'pending'),
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
            'index' => Pages\ListPembelianSayas::route('/'),
            // 'view' => Pages\ViewPembelianSaya::route('/{record}'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        // Hanya menampilkan pembelian yang dilakukan oleh user yang login
        return parent::getEloquentQuery()->where('pembeli_id', Auth::id());
    }
}
