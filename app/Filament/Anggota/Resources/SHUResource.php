<?php

namespace App\Filament\Anggota\Resources;

use App\Filament\Anggota\Resources\SHUResource\Pages;
use App\Models\SHUDistribution;
use App\Services\SHUClaimService;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class SHUResource extends Resource
{
    protected static ?string $model = SHUDistribution::class;
    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationLabel = 'SHU Saya';
    protected static ?string $pluralModelLabel = 'SHU Saya';
    protected static ?string $navigationGroup = 'Keuangan';
    protected static ?int $navigationSort = 5;
    protected static ?string $slug = 'shu';

    public static function canAccess(): bool
    {
        return Auth::user()->hasRole('anggota_tetap');
    }

    public static function getEloquentQuery(): Builder
    {
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
                Tables\Columns\TextColumn::make('total_biaya_admin')
                    ->label('Biaya Admin')
                    ->money('IDR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_bunga_pinjaman')
                    ->label('Bunga Pinjaman')
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

                Tables\Columns\IconColumn::make('is_claimed')
                    ->label('Status Pengambilan')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('warning')
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('claimed_at')
                    ->label('Waktu Pengambilan')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->placeholder('-')
                    ->visible(fn($record): bool => $record && $record->is_claimed),

                Tables\Columns\TextColumn::make('tanggal_distribusi')
                    ->label('Tanggal Distribusi')
                    ->dateTime('d M Y')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('tahun')
                    ->options(
                        fn() => SHUDistribution::where('user_id', Auth::id())
                            ->where('status', 'dibagikan')
                            ->distinct()
                            ->pluck('tahun', 'tahun')
                            ->toArray()
                    ),
                Tables\Filters\Filter::make('is_claimed')
                    ->label('Status Pengambilan')
                    ->form([
                        Forms\Components\Select::make('status_ambil')
                            ->label('Status Pengambilan')
                            ->options([
                                'sudah' => 'Sudah Diambil',
                                'belum' => 'Belum Diambil',
                            ]),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                isset($data['status_ambil']) && $data['status_ambil'] === 'sudah',
                                fn(Builder $query): Builder => $query->where('is_claimed', true),
                            )
                            ->when(
                                isset($data['status_ambil']) && $data['status_ambil'] === 'belum',
                                fn(Builder $query): Builder => $query->where('is_claimed', false),
                            );
                    })
            ])
            ->actions([
                Tables\Actions\Action::make('ambilDana')
                    ->label('Ambil Dana')
                    ->icon('heroicon-o-banknotes')
                    ->color('success')
                    ->visible(fn(SHUDistribution $record): bool => $record && !$record->is_claimed)
                    ->requiresConfirmation()
                    ->modalHeading('Ambil Dana SHU')
                    ->modalDescription(
                        fn(SHUDistribution $record): string =>
                        "Anda akan mengambil dana SHU sebesar Rp " . number_format($record->jumlah_shu, 0, ',', '.') .
                            " untuk tahun " . $record->tahun . ". Dana akan ditambahkan ke saldo Anda."
                    )
                    ->modalSubmitActionLabel('Ya, Ambil Dana')
                    ->modalIcon('heroicon-o-banknotes')
                    ->action(function (SHUDistribution $record, Tables\Actions\Action $action) {
                        $service = new SHUClaimService();
                        $result = $service->claimSHU($record);

                        if ($result['success']) {
                            Notification::make()
                                ->title('Dana SHU Berhasil Diambil')
                                ->body("Dana SHU sebesar Rp " . number_format($result['amount'], 0, ',', '.') .
                                    " telah ditambahkan ke saldo Anda. Saldo sekarang: Rp " .
                                    number_format($result['new_balance'], 0, ',', '.'))
                                ->success()
                                ->send();

                            // Refresh halaman dengan cara redirect ke halaman yang sama
                            redirect(request()->header('Referer'));
                        } else {
                            Notification::make()
                                ->title('Gagal Mengambil Dana')
                                ->body($result['message'])
                                ->danger()
                                ->send();
                        }
                    }),
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
            'index' => Pages\ListSHU::route('/'),
        ];
    }
}
