<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProdukResource\Pages;
use App\Filament\Resources\ProdukResource\RelationManagers;
use App\Models\Produk;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProdukResource extends Resource
{
    protected static ?string $model = Produk::class;
    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';
    protected static ?string $navigationLabel = 'Produk Koperasi';
    protected static ?string $navigationGroup = 'Manajemen Produk';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Produk')
                    ->schema([
                        Forms\Components\TextInput::make('nama')
                            ->label('Nama Produk')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Select::make('kategori')
                            ->label('Kategori')
                            ->options([
                                'Sembako' => 'Sembako',
                                'Alat Tulis' => 'Alat Tulis',
                                'Elektronik' => 'Elektronik',
                                'Makanan' => 'Makanan',
                                'Minuman' => 'Minuman',
                                'Lainnya' => 'Lainnya',
                            ])
                            ->searchable(),

                        Forms\Components\TextInput::make('harga')
                            ->label('Harga')
                            ->required()
                            ->numeric()
                            ->prefix('Rp'),

                        Forms\Components\TextInput::make('stok')
                            ->label('Stok')
                            ->required()
                            ->integer()
                            ->default(0)
                            ->minValue(0),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Detail Produk')
                    ->schema([
                        Forms\Components\FileUpload::make('gambar')
                            ->label('Gambar Produk')
                            ->image()
                            ->directory('produk-koperasi')
                            ->disk('public')
                            ->imageEditor()
                            ->imageResizeMode('cover')
                            ->imageCropAspectRatio('1:1')
                            ->imageResizeTargetWidth('600')
                            ->imageResizeTargetHeight('600'),

                        Forms\Components\RichEditor::make('deskripsi')
                            ->label('Deskripsi Produk')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                                'link',
                            ])
                            ->columnSpanFull(),

                        Forms\Components\Toggle::make('aktif')
                            ->label('Status Aktif')
                            ->default(true)
                            ->helperText('Produk tidak aktif tidak akan ditampilkan di katalog'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('gambar')
                    ->label('Gambar')
                    ->disk('public')
                    ->square()
                    ->size(70),

                Tables\Columns\TextColumn::make('nama')
                    ->label('Nama Produk')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('kategori')
                    ->label('Kategori')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('harga')
                    ->label('Harga')
                    ->money('IDR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('stok')
                    ->label('Stok')
                    ->sortable(),

                Tables\Columns\IconColumn::make('aktif')
                    ->label('Status')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Terakhir Diupdate')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('kategori')
                    ->options([
                        'Sembako' => 'Sembako',
                        'Alat Tulis' => 'Alat Tulis',
                        'Elektronik' => 'Elektronik',
                        'Makanan' => 'Makanan',
                        'Minuman' => 'Minuman',
                        'Lainnya' => 'Lainnya',
                    ])
                    ->multiple(),

                Tables\Filters\SelectFilter::make('aktif')
                    ->label('Status')
                    ->options([
                        true => 'Aktif',
                        false => 'Tidak Aktif',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('updateStock')
                        ->label('Update Stok')
                        ->icon('heroicon-o-adjustments-horizontal')
                        ->form([
                            Forms\Components\Select::make('action')
                                ->label('Tindakan')
                                ->options([
                                    'add' => 'Tambah Stok',
                                    'subtract' => 'Kurangi Stok',
                                    'set' => 'Set Stok',
                                ])
                                ->required(),
                            Forms\Components\TextInput::make('amount')
                                ->label('Jumlah')
                                ->numeric()
                                ->required()
                                ->minValue(0),
                        ])
                        ->action(function (array $data, Collection $records): void {
                            foreach ($records as $record) {
                                if ($data['action'] === 'add') {
                                    $record->stok += $data['amount'];
                                } elseif ($data['action'] === 'subtract') {
                                    $record->stok = max(0, $record->stok - $data['amount']);
                                } else {
                                    $record->stok = $data['amount'];
                                }
                                $record->save();
                            }
                        }),
                    Tables\Actions\BulkAction::make('toggleActive')
                        ->label('Ubah Status')
                        ->icon('heroicon-o-eye')
                        ->requiresConfirmation()
                        ->action(function (Collection $records): void {
                            foreach ($records as $record) {
                                $record->aktif = !$record->aktif;
                                $record->save();
                            }
                        }),
                ]),
            ])
            ->defaultSort('updated_at', 'desc');
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
            'index' => Pages\ListProduks::route('/'),
            'create' => Pages\CreateProduk::route('/create'),
            'edit' => Pages\EditProduk::route('/{record}/edit'),
        ];
    }
}
