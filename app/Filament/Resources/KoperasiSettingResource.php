<?php
// filepath: d:\Development\menpro\koperasi_kita\app\Filament\Resources\KoperasiSettingResource.php

namespace App\Filament\Resources;

use App\Filament\Resources\KoperasiSettingResource\Pages;
use App\Models\KoperasiSetting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Cache;

class KoperasiSettingResource extends Resource
{
    protected static ?string $model = KoperasiSetting::class;

    protected static ?string $navigationIcon = 'heroicon-o-cog';
    protected static ?string $navigationLabel = 'Pengaturan Koperasi';
    protected static ?string $modelLabel = 'Pengaturan Koperasi';
    protected static ?string $navigationGroup = 'Sistem';
    protected static ?int $navigationSort = 100;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Hidden::make('key'),

                Forms\Components\TextInput::make('description')
                    ->label('Deskripsi')
                    ->disabled()
                    ->columnSpanFull(),

                // Form Input Value sesuai tipe
                Forms\Components\Group::make()
                    ->schema(function ($record) {
                        if ($record && $record->key === 'biaya_admin_percent') {
                            // Pengaturan khusus untuk biaya admin (persentase)
                            return [
                                Forms\Components\TextInput::make('value')
                                    ->label('Nilai')
                                    ->required()
                                    ->numeric()
                                    ->minValue(0.1)
                                    ->maxValue(100)
                                    ->step(0.1)
                                    ->suffix('%')
                                    ->inputMode('decimal')
                                    ->placeholder('1.5')
                                    ->afterStateHydrated(function ($component, $state) {
                                        // Format nilai ke angka desimal 1 digit untuk tampilan
                                        $component->state(number_format((float)$state, 1, '.', ''));
                                    })
                                    ->beforeStateDehydrated(function ($component, $state) {
                                        // Pastikan state berupa numeric string tanpa format sebelum disimpan
                                        $numericValue = (float)$state;
                                        $component->state(number_format($numericValue, 1, '.', ''));
                                    }),
                            ];
                        } else {
                            // Pengaturan untuk nominal uang (simpanan pokok, simpanan wajib)
                            return [
                                Forms\Components\TextInput::make('value')
                                    ->label('Nilai')
                                    ->required()
                                    ->numeric()
                                    ->minValue(1000)
                                    ->prefix('Rp')
                                    ->mask(RawJs::make('$money($input)'))->stripCharacters(',')
                                    ->beforeStateDehydrated(function ($component, $state) {
                                        // Pastikan state berupa numeric string tanpa format sebelum disimpan
                                        $numericValue = preg_replace('/[^0-9]/', '', $state);
                                        $component->state($numericValue);
                                    }),
                            ];
                        }
                    }),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('key')
                    ->label('Kunci')
                    ->searchable(),

                Tables\Columns\TextColumn::make('description')
                    ->label('Deskripsi')
                    ->searchable(),

                Tables\Columns\TextColumn::make('value')
                    ->label('Nilai')
                    ->formatStateUsing(function ($state, $record) {
                        if ($record->key === 'biaya_admin_percent') {
                            return number_format((float)$state, 1) . '%';
                        } else {
                            return 'Rp ' . number_format((float)$state, 0, ',', '.');
                        }
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Terakhir Diperbarui')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->mutateRecordDataUsing(function (array $data): array {
                        // Clean up the value before editing
                        $data['value'] = preg_replace('/[^0-9.]/', '', $data['value']);
                        return $data;
                    })
                    ->successNotification(null) // Nonaktifkan notifikasi default
                    ->after(function ($record, $data) {
                        // Hapus cache
                        try {
                            Cache::forget('koperasi_setting.' . $record->key);
                        } catch (\Exception $e) {
                            // Log error jika terjadi masalah saat menghapus cache
                            \Illuminate\Support\Facades\Log::error("Error clearing cache: " . $e->getMessage());
                        }

                        // Buat notifikasi sukses secara manual
                        try {
                            $displayValue = '';

                            if ($record->key === 'biaya_admin_percent') {
                                $displayValue = number_format((float)$record->value, 1) . '%';
                            } else {
                                $displayValue = 'Rp ' . number_format((int)$record->value, 0, ',', '.');
                            }

                            $notificationTitle = '';

                            if ($record->key === 'simpanan_pokok_amount') {
                                $notificationTitle = "Simpanan pokok diubah menjadi {$displayValue}";
                            } elseif ($record->key === 'simpanan_wajib_amount') {
                                $notificationTitle = "Simpanan wajib diubah menjadi {$displayValue}";
                            } elseif ($record->key === 'biaya_admin_percent') {
                                $notificationTitle = "Biaya admin diubah menjadi {$displayValue}";
                            } else {
                                $notificationTitle = "Pengaturan berhasil diperbarui";
                            }

                            Notification::make()
                                ->title($notificationTitle)
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            // Log error jika terjadi masalah saat membuat notifikasi
                            \Illuminate\Support\Facades\Log::error("Error creating notification: " . $e->getMessage());
                        }
                    }),
            ])
            ->bulkActions([]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListKoperasiSetting::route('/'),
        ];
    }
    public static function canCreate(): bool
    {
        return false;
    }
}
