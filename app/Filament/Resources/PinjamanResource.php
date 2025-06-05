<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PinjamanResource\Pages;
use App\Filament\Resources\PinjamanResource\RelationManagers;
use App\Models\Pinjaman;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class PinjamanResource extends Resource
{
    protected static ?string $model = Pinjaman::class;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel = 'Persetujuan Pinjaman';
    protected static ?string $pluralModelLabel = 'pinjaman';
    protected static ?string $slug = 'pinjaman';
    public static function canCreate(): bool
    {
        return false;
    }
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Detail Peminjam')
                    ->schema([
                        Forms\Components\TextInput::make('user.name')
                            ->label('Nama Anggota')
                            ->disabled(),

                        Forms\Components\TextInput::make('user.email')
                            ->label('Email')
                            ->disabled(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Informasi Pinjaman')
                    ->schema([
                        Forms\Components\TextInput::make('jumlah')
                            ->label('Jumlah Pinjaman')
                            ->disabled()
                            ->prefix('Rp'),

                        Forms\Components\TextInput::make('tenorPinjaman.nama')
                            ->label('Tenor')
                            ->disabled(),

                        Forms\Components\TextInput::make('tenorPinjaman.bunga')
                            ->label('Bunga')
                            ->disabled()
                            ->suffix('%'),

                        Forms\Components\TextInput::make('tujuan')
                            ->label('Tujuan Pinjaman')
                            ->disabled(),

                        Forms\Components\Textarea::make('keterangan')
                            ->label('Keterangan')
                            ->disabled()
                            ->columnSpanFull(),

                        Forms\Components\DatePicker::make('tanggal_pengajuan')
                            ->label('Tanggal Pengajuan')
                            ->disabled(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Informasi Simulasi')
                    ->schema([
                        Forms\Components\TextInput::make('angsuran_per_bulan')
                            ->label('Angsuran per Bulan')
                            ->disabled()
                            ->prefix('Rp')
                            ->formatStateUsing(fn(Pinjaman $record): string => number_format($record->angsuran_per_bulan, 0, ',', '.')),

                        Forms\Components\TextInput::make('total_pembayaran')
                            ->label('Total Pembayaran')
                            ->disabled()
                            ->prefix('Rp')
                            ->formatStateUsing(fn(Pinjaman $record): string => number_format($record->total_pembayaran, 0, ',', '.')),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Persetujuan')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->required()
                            ->options([
                                'pending' => 'Pending',
                                'disetujui' => 'Disetujui',
                                'ditolak' => 'Ditolak',
                            ])
                            ->default('pending'),

                        Forms\Components\Textarea::make('alasan_penolakan')
                            ->label('Alasan Penolakan (jika ditolak)')
                            ->required(fn(Forms\Get $get): bool => $get('status') === 'ditolak')
                            ->hidden(fn(Forms\Get $get): bool => $get('status') !== 'ditolak'),
                    ]),
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

                Tables\Columns\TextColumn::make('tanggal_pengajuan')
                    ->label('Tanggal Pengajuan')
                    ->date('d M Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('jumlah')
                    ->label('Jumlah Pinjaman')
                    ->money('IDR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('tenorPinjaman.nama')
                    ->label('Tenor')
                    ->description(fn(Pinjaman $record): string => 'Bunga: ' . $record->nilai_bunga . '%')
                    ->sortable(),

                Tables\Columns\TextColumn::make('angsuran_per_bulan')
                    ->label('Angsuran/Bulan')
                    ->money('IDR'),

                Tables\Columns\TextColumn::make('tujuan')
                    ->label('Tujuan'),

                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'disetujui',
                        'danger' => 'ditolak',
                    ]),
            ])
            ->filters([
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
                    ->visible(fn(Pinjaman $record) => $record->status === 'pending')
                    ->action(function (Pinjaman $record) {
                        $record->update([
                            'status' => 'disetujui',
                            'tanggal_persetujuan' => now(),
                            'disetujui_oleh' => Auth::user()->name,
                        ]);
                    }),

                Tables\Actions\Action::make('reject')
                    ->label('Tolak')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->visible(fn(Pinjaman $record) => $record->status === 'pending')
                    ->form([
                        Forms\Components\Textarea::make('alasan_penolakan')
                            ->label('Alasan Penolakan')
                            ->required(),
                    ])
                    ->action(function (Pinjaman $record, array $data) {
                        $record->update([
                            'status' => 'ditolak',
                            'alasan_penolakan' => $data['alasan_penolakan'],
                            'tanggal_persetujuan' => now(),
                            'disetujui_oleh' => Auth::user()->name,
                        ]);
                    }),

                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListPinjamen::route('/'),
            'create' => Pages\CreatePinjaman::route('/create'),
            'edit' => Pages\EditPinjaman::route('/{record}/edit'),
        ];
    }
}
