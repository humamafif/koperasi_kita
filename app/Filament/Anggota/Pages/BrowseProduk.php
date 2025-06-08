<?php

namespace App\Filament\Anggota\Pages;

use App\Models\PembelianProduk;
use App\Models\Produk;
use App\Models\User;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Actions\Action;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class BrowseProduk extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';
    protected static ?string $navigationLabel = 'Katalog Produk';
    protected static ?string $navigationGroup = 'Produk';
    protected static ?int $navigationSort = 3;
    protected static string $view = 'filament.anggota.pages.browse-produk';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Produk::query()
                    ->where('aktif', true)
                    ->where('user_id', '!=', Auth::id()) // Jangan tampilkan produk milik user sendiri
            )
            ->columns([
                ImageColumn::make('gambar')
                    ->label('Gambar')
                    ->disk('public')
                    ->square()
                    ->size(70),

                TextColumn::make('nama')
                    ->label('Nama Produk')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('user.name')
                    ->label('Penjual')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('kategori')
                    ->label('Kategori')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('deskripsi')
                    ->label('Deskripsi')
                    ->formatStateUsing(fn($state) => strip_tags($state))
                    ->limit(30)
                    ->tooltip(function ($record) {
                        return strip_tags($record->deskripsi);
                    }),

                TextColumn::make('harga')
                    ->label('Harga')
                    ->money('IDR')
                    ->sortable(),

                TextColumn::make('stok')
                    ->label('Stok')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('kategori')
                    ->options([
                        'Sembako' => 'Sembako',
                        'Alat Tulis' => 'Alat Tulis',
                        'Elektronik' => 'Elektronik',
                        'Makanan' => 'Makanan',
                        'Minuman' => 'Minuman',
                        'Lainnya' => 'Lainnya',
                    ])
                    ->multiple(),

                SelectFilter::make('user_id')
                    ->label('Penjual')
                    ->options(function () {
                        return User::join('produks', 'users.id', '=', 'produks.user_id')
                            ->where('produks.aktif', true)
                            ->where('users.id', '!=', Auth::id())
                            ->distinct()
                            ->pluck('users.name', 'users.id')
                            ->toArray();
                    })
                    ->searchable(),
            ])
            ->actions([
                Action::make('beli')
                    ->label('Beli')
                    ->icon('heroicon-o-shopping-cart')
                    ->color('primary')
                    ->form([
                        TextInput::make('jumlah')
                            ->label('Jumlah')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->default(1)
                            ->maxValue(function (Produk $record) {
                                return $record->stok;
                            }),

                        Textarea::make('catatan')
                            ->label('Catatan')
                            ->maxLength(500),
                    ])
                    ->action(function (Produk $record, array $data) {
                        // Periksa apakah stok mencukupi
                        if ($record->stok < $data['jumlah']) {
                            Notification::make()
                                ->title('Stok tidak mencukupi')
                                ->danger()
                                ->send();

                            return;
                        }

                        // Buat pembelian baru
                        $pembelian = PembelianProduk::create([
                            'produk_id' => $record->id,
                            'pembeli_id' => Auth::id(),
                            'penjual_id' => $record->user_id,
                            'jumlah' => $data['jumlah'],
                            'harga_satuan' => $record->harga,
                            'total' => $record->harga * $data['jumlah'],
                            'status' => 'pending',
                            'catatan' => $data['catatan'] ?? null,
                        ]);

                        // Kurangi stok produk
                        $record->stok -= $data['jumlah'];
                        $record->save();

                        // Tampilkan notifikasi sukses
                        Notification::make()
                            ->title('Pembelian berhasil')
                            ->body('Pembelian Anda sedang diproses oleh penjual.')
                            ->success()
                            ->send();
                    })
                    ->visible(fn(Produk $record) => $record->stok > 0)
                    ->requiresConfirmation()
            ])
            ->emptyStateHeading('Tidak ada produk')
            ->emptyStateDescription('Tidak ada produk yang tersedia saat ini.')
            ->defaultSort('created_at', 'desc');
    }
}
