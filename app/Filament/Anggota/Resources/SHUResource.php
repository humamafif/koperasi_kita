<?php

namespace App\Filament\Anggota\Resources;

use App\Filament\Anggota\Resources\SHUResource\Pages;
use App\Models\SHUDistribution;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class SHUResource extends Resource
{
    protected static ?string $model = SHUDistribution::class;
    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationLabel = 'SHU Saya';
    protected static ?string $navigationGroup = 'Keuangan';
    protected static ?int $navigationSort = 5;

    public static function getEloquentQuery(): Builder
    {
        // Hanya tampilkan SHU milik anggota yang login
        return parent::getEloquentQuery()
            ->where('user_id', Auth::id())
            ->where('status', 'dibagikan');
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('tahun')
                    ->label('Tahun')
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_simpanan')
                    ->label('Total Simpanan')
                    ->money('IDR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('persentase_kontribusi')
                    ->label('Persentase Kontribusi')
                    ->formatStateUsing(fn(float $state): string => number_format($state * 100, 2) . '%')
                    ->sortable(),

                Tables\Columns\TextColumn::make('jumlah_shu')
                    ->label('Jumlah SHU')
                    ->money('IDR')
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'success' => 'dibagikan',
                        'warning' => 'pending',
                        'danger' => 'ditolak',
                    ]),

                Tables\Columns\TextColumn::make('tanggal_distribusi')
                    ->label('Tanggal Distribusi')
                    ->dateTime('d M Y')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('tahun')
                    ->options(
                        fn() => SHUDistribution::where('user_id', Auth::id())
                            ->distinct()
                            ->pluck('tahun', 'tahun')
                            ->toArray()
                    ),
            ])
            ->defaultSort('tahun', 'desc');
    }

    public static function canCreate(): bool
    {
        return false; // Anggota tidak diizinkan membuat SHU
    }

    public static function canEdit(Model $record): bool
    {
        return false; // Anggota tidak diizinkan mengedit SHU
    }

    public static function canDelete(Model $record): bool
    {
        return false; // Anggota tidak diizinkan menghapus SHU
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSHUS::route('/'),
            'view' => Pages\ViewSHU::route('/{record}'),
        ];
    }
}
