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

    public static function getNavigationBadge(): ?string
    {
        $count = PendaftaranAnggotaTetap::where('status', 'pending')->count();
        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Tagihan menunggu verifikasi';
    }

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
                        Forms\Components\TextInput::make('name')
                            ->label('Nama')
                            ->disabled()
                            ->afterStateHydrated(function ($component, $state, $record) {
                                if ($record && $record->user) {
                                    $component->state($record->user->name);
                                }
                            }),

                        Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->disabled()
                            ->afterStateHydrated(function ($component, $state, $record) {
                                if ($record && $record->user) {
                                    $component->state($record->user->email);
                                }
                            }),

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
                        Forms\Components\View::make('components.view-bukti-pembayaran')
                            ->columnSpanFull(),


                        Forms\Components\DatePicker::make('tanggal_pengajuan')
                            ->label('Tanggal Pengajuan')
                            ->displayFormat('d M Y')
                            ->disabled(),
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
            ->recordUrl(fn($record) => null)
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

                Tables\Columns\ImageColumn::make('bukti_pembayaran')
                    ->label('Bukti Pembayaran')
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

                        \App\Models\RiwayatTransaksi::where('user_id', $record->user_id)
                            ->where('jenis_transaksi', 'simpanan_pokok')
                            ->where('status', 'pending')
                            ->update([
                                'status' => 'disetujui',
                            ]);

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
                // Tables\Actions\EditAction::make(),
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
            // 'edit' => Pages\EditPendaftaranAnggotaTetap::route('/{record}/edit'),
        ];
    }
}
