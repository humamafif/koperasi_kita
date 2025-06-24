<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TenorPinjamanResource\Pages;
use App\Filament\Resources\TenorPinjamanResource\RelationManagers;
use App\Models\TenorPinjaman;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TenorPinjamanResource extends Resource
{
    protected static ?string $model = TenorPinjaman::class;
    protected static ?string $navigationGroup = 'Simpanan & Pinjaman';
    protected static ?string $navigationIcon = 'heroicon-o-calendar';
    protected static ?string $navigationLabel = 'Tenor & Bunga Pinjaman';
    protected static ?string $pluralModelLabel = 'tenor & bunga pinjaman';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('durasi')
                    ->label('Durasi (Bulan)')
                    ->required()
                    ->numeric()
                    ->minValue(1)
                    ->maxValue(120)
                    ->suffix('bulan'),

                Forms\Components\TextInput::make('nama')
                    ->label('Nama Tenor')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('bunga')
                    ->label('Bunga (%)')
                    ->required()
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(100)
                    ->suffix('%')
                    ->helperText('Bunga pinjaman untuk tenor ini'),

                Forms\Components\Textarea::make('keterangan')
                    ->label('Keterangan')
                    ->rows(3)
                    ->maxLength(255),

                Forms\Components\Toggle::make('aktif')
                    ->label('Status Aktif')
                    ->default(true)
                    ->helperText('Tenor tidak aktif tidak akan ditampilkan di form pengajuan pinjaman'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('durasi')
                    ->label('Durasi')
                    ->suffix(' bulan')
                    ->sortable(),

                Tables\Columns\TextColumn::make('nama')
                    ->label('Nama Tenor')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('bunga')
                    ->label('Bunga')
                    ->suffix('%')
                    ->sortable(),

                Tables\Columns\TextColumn::make('keterangan')
                    ->label('Keterangan')
                    ->limit(50),

                Tables\Columns\IconColumn::make('aktif')
                    ->label('Status')
                    ->boolean()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('aktif')
                    ->label('Status')
                    ->options([
                        '1' => 'Aktif',
                        '0' => 'Tidak Aktif',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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
            'index' => Pages\ListTenorPinjamen::route('/'),
            'create' => Pages\CreateTenorPinjaman::route('/create'),
            'edit' => Pages\EditTenorPinjaman::route('/{record}/edit'),
        ];
    }
}
