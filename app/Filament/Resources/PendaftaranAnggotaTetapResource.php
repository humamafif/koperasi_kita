<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PendaftaranAnggotaTetapResource\Pages;
use App\Filament\Resources\PendaftaranAnggotaTetapResource\RelationManagers;
use App\Models\PendaftaranAnggotaTetap;
use App\Models\Simpanan;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;

class PendaftaranAnggotaTetapResource extends Resource
{
    protected static ?string $model = PendaftaranAnggotaTetap::class;
    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationLabel = 'Pendaftaran Anggota Tetap';
    protected static ?string $pluralModelLabel = 'pendaftaran anggota tetap';
    protected static ?string $navigationGroup = 'Keanggotaan';

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
                        Forms\Components\TextInput::make('user.name')
                            ->label('Nama')
                            ->disabled(),

                        Forms\Components\TextInput::make('user.email')
                            ->label('Email')
                            ->disabled(),

                        Forms\Components\TextInput::make('nik')
                            ->label('NIK')
                            ->disabled(),

                        Forms\Components\TextInput::make('no_telepon')
                            ->label('Nomor Telepon')
                            ->disabled(),

                        Forms\Components\Textarea::make('alamat')
                            ->label('Alamat')
                            ->disabled()
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Simpanan Pokok')
                    ->schema([
                        Forms\Components\FileUpload::make('bukti_pembayaran')
                            ->label('Bukti Pembayaran')
                            ->disabled()
                            ->image()
                            ->disk('public')
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('tanggal_pengajuan')
                            ->label('Tanggal Pengajuan')
                            ->disabled()
                            ->formatStateUsing(fn($state) => $state ? $state->format('d M Y') : '-'),
                    ]),

                Forms\Components\Section::make('Verifikasi')
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

                        Forms\Components\Textarea::make('keterangan')
                            ->label('Keterangan')
                            ->required(fn(Forms\Get $get): bool => $get('status') === 'ditolak')
                            ->hidden(fn(Forms\Get $get): bool => $get('status') === 'pending')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('user.email')
                    ->label('Email')
                    ->searchable(),

                Tables\Columns\TextColumn::make('tanggal_pengajuan')
                    ->label('Tanggal Pengajuan')
                    ->date('d M Y')
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'disetujui',
                        'danger' => 'ditolak',
                    ]),

                Tables\Columns\TextColumn::make('tanggal_verifikasi')
                    ->label('Tanggal Verifikasi')
                    ->date('d M Y')
                    ->sortable(),
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
                    ->visible(fn(PendaftaranAnggotaTetap $record) => $record->status === 'pending')
                    ->action(function (PendaftaranAnggotaTetap $record) {
                        // Update status pendaftaran
                        $record->update([
                            'status' => 'disetujui',
                            'tanggal_verifikasi' => now(),
                            'diverifikasi_oleh' => Auth::user()->name,
                        ]);

                        // Update status simpanan pokok
                        Simpanan::where('user_id', $record->user_id)
                            ->where('jenis', 'pokok')
                            ->where('status', 'pending')
                            ->update([
                                'status' => 'disetujui',
                                'tanggal_verifikasi' => now(),
                                'diverifikasi_oleh' => Auth::user()->name,
                            ]);

                        // Update data user
                        $user = User::find($record->user_id);
                        $user->update([
                            'nik' => $record->nik,
                            'alamat' => $record->alamat,
                            'no_telepon' => $record->no_telepon,
                            'is_anggota_tetap' => true,
                            'tanggal_menjadi_anggota_tetap' => now(),
                        ]);

                        // Tambahkan role anggota_tetap
                        $role = Role::findByName('anggota_tetap');
                        $user->assignRole($role);
                    }),

                Tables\Actions\Action::make('reject')
                    ->label('Tolak')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->visible(fn(PendaftaranAnggotaTetap $record) => $record->status === 'pending')
                    ->form([
                        Forms\Components\Textarea::make('keterangan')
                            ->label('Alasan Penolakan')
                            ->required(),
                    ])
                    ->action(function (PendaftaranAnggotaTetap $record, array $data) {
                        // Update status pendaftaran
                        $record->update([
                            'status' => 'ditolak',
                            'keterangan' => $data['keterangan'],
                            'tanggal_verifikasi' => now(),
                            'diverifikasi_oleh' => Auth::user()->name,
                        ]);

                        // Update status simpanan pokok
                        Simpanan::where('user_id', $record->user_id)
                            ->where('jenis', 'pokok')
                            ->where('status', 'pending')
                            ->update([
                                'status' => 'ditolak',
                                'keterangan' => $data['keterangan'],
                                'tanggal_verifikasi' => now(),
                                'diverifikasi_oleh' => Auth::user()->name,
                            ]);
                    }),

                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->defaultSort('tanggal_pengajuan', 'desc');
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
            'index' => Pages\ListPendaftaranAnggotaTetaps::route('/'),
            'edit' => Pages\EditPendaftaranAnggotaTetap::route('/{record}/edit'),
        ];
    }
}
