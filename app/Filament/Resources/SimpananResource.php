<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SimpananResource\Pages;
use App\Filament\Resources\SimpananResource\RelationManagers;
use App\Models\Simpanan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class SimpananResource extends Resource
{
protected static ?string $model = Simpanan::class;
    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationLabel = 'Simpanan';
    protected static ?string $pluralModelLabel = 'simpanan';
    protected static ?string $navigationGroup = 'Simpanan & Pinjaman';
    protected static ?int $navigationSort = 1;

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

                Forms\Components\Section::make('Informasi Simpanan')
                    ->schema([
                        Forms\Components\Select::make('jenis')
                            ->label('Jenis Simpanan')
                            ->options([
                                'pokok' => 'Simpanan Pokok',
                                'wajib' => 'Simpanan Wajib',
                                'sukarela' => 'Simpanan Sukarela',
                            ])
                            ->required(),

                        Forms\Components\TextInput::make('jumlah')
                            ->label('Jumlah')
                            ->required()
                            ->numeric()
                            ->prefix('Rp'),

                        Forms\Components\DatePicker::make('tanggal_pembayaran')
                            ->label('Tanggal Pembayaran')
                            ->required()
                            ->default(now()),

                        Forms\Components\TextInput::make('bukti_pembayaran')
                            ->label('Bukti Pembayaran')
                            ->prefix('storage/')
                            ->suffixAction(
                                Forms\Components\Actions\Action::make('view_image')
                                    ->icon('heroicon-o-photo')
                                    ->url(fn($record) => $record && $record->bukti_pembayaran
                                        ? asset('storage/' . $record->bukti_pembayaran)
                                        : null, true)
                                    ->visible(fn($record) => $record && $record->bukti_pembayaran)
                            )
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Verifikasi')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'pending' => 'Pending',
                                'disetujui' => 'Disetujui',
                                'ditolak' => 'Ditolak',
                            ])
                            ->required()
                            ->default('pending'),

                        Forms\Components\Textarea::make('keterangan')
                            ->label('Keterangan')
                            ->required(fn(Forms\Get $get): bool => $get('status') === 'ditolak')
                            ->hidden(fn(Forms\Get $get): bool => $get('status') === 'pending'),
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

                Tables\Columns\TextColumn::make('jenis')
                    ->label('Jenis Simpanan')
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'pokok' => 'Simpanan Pokok',
                        'wajib' => 'Simpanan Wajib',
                        'sukarela' => 'Simpanan Sukarela',
                        default => $state,
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('jumlah')
                    ->label('Jumlah')
                    ->money('IDR')
                    ->sortable(),

                Tables\Columns\ImageColumn::make('bukti_pembayaran')
                    ->label('Bukti Pembayaran')
                    ->disk('public')
                    ->visibility('public')
                    ->size(60)
                    ->square()
                    ->extraImgAttributes([
                        'alt' => 'Bukti Pembayaran',
                        'loading' => 'lazy',
                    ])
                    ->wrap(),
                Tables\Columns\TextColumn::make('tanggal_pembayaran')
                    ->label('Tanggal Pembayaran')
                    ->date('d M Y')
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'disetujui',
                        'danger' => 'ditolak',
                    ]),
                Tables\Columns\TextColumn::make('keterangan')
                    ->label('Keterangan')
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('jenis')
                    ->options([
                        'pokok' => 'Simpanan Pokok',
                        'wajib' => 'Simpanan Wajib',
                        'sukarela' => 'Simpanan Sukarela',
                    ]),

                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'disetujui' => 'Disetujui',
                        'ditolak' => 'Ditolak',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('approve')
                    ->label('Setujui')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->visible(fn(Simpanan $record) => $record->status === 'pending')
                    ->action(function (Simpanan $record) {
                        $record->update([
                            'status' => 'disetujui',
                            'tanggal_verifikasi' => now(),
                            'diverifikasi_oleh' => Auth::user()->name,
                        ]);
                    }),

                Tables\Actions\Action::make('reject')
                    ->label('Tolak')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->visible(fn(Simpanan $record) => $record->status === 'pending')
                    ->form([
                        Forms\Components\Textarea::make('keterangan')
                            ->label('Alasan Penolakan')
                            ->required(),
                    ])
                    ->action(function (Simpanan $record, array $data) {
                        $record->update([
                            'status' => 'ditolak',
                            'keterangan' => $data['keterangan'],
                            'tanggal_verifikasi' => now(),
                            'diverifikasi_oleh' => Auth::user()->name,
                        ]);
                    }),
                Tables\Actions\ViewAction::make(),
                // Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('tanggal_pembayaran', 'desc');
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
            'index' => Pages\ListSimpanans::route('/'),
            // 'create' => Pages\CreateSimpanan::route('/create'),
            // 'edit' => Pages\EditSimpanan::route('/{record}/edit'),
        ];
    }
}
