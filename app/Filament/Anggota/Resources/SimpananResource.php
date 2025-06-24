<?php

namespace App\Filament\Anggota\Resources;

use App\Filament\Anggota\Resources\SimpananResource\Pages;
use App\Filament\Anggota\Resources\SimpananResource\RelationManagers;
use App\Models\Simpanan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class SimpananResource extends Resource
{
    protected static ?string $model = Simpanan::class;
    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationLabel = 'Simpanan';
    protected static ?string $pluralModelLabel = 'simpanan';
    protected static ?string $slug = 'simpanan';

    public static function canAccess(): bool
    {
        return Auth::user()->hasRole('anggota_tetap');
    }
    public static function canCreate(): bool
    {
        return Auth::user()->can('create_saving');
    }



    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Simpanan')
                    ->schema([
                        Forms\Components\Select::make('jenis')
                            ->label('Jenis Simpanan')
                            ->options([
                                // 'wajib' => 'Simpanan Wajib',
                                'sukarela' => 'Simpanan Sukarela',
                            ])
                            ->required()
                            ->default('sukarela')
                            ->reactive(),

                        Forms\Components\TextInput::make('jumlah')
                            ->label('Jumlah')
                            ->required()
                            ->numeric()
                            ->prefix('Rp')
                            ->mask(RawJs::make('$money($input)'))->stripCharacters(',')
                            ->default(function (Forms\Get $get) {
                                return $get('jenis') === 'wajib'
                                    ? \App\Models\KoperasiSetting::getSimpananWajibAmount()
                                    : null;
                            })
                            ->readOnly(function (Forms\Get $get) {
                                return $get('jenis') === 'wajib';
                            })
                            ->helperText(function (Forms\Get $get) {
                                $simpananWajibAmount = \App\Models\KoperasiSetting::getSimpananWajibAmount();
                                return $get('jenis') === 'wajib'
                                    ? 'Simpanan wajib sebesar Rp ' . number_format($simpananWajibAmount, 0, ',', '.') . ' per bulan'
                                    : 'Masukkan jumlah simpanan sukarela';
                            }),

                        Forms\Components\Textarea::make('keterangan')
                            ->label('Keterangan')
                            ->placeholder('Masukkan keterangan jika diperlukan')
                            ->columnSpanFull(),
                        Forms\Components\Section::make('Informasi Pembayaran')
                            ->schema([
                                Forms\Components\View::make('filament.components.rekening-info')
                                    ->label('Informasi Rekening')
                                    ->columnSpanFull(),
                                Forms\Components\FileUpload::make('bukti_pembayaran')
                                    ->label('Bukti Pembayaran')
                                    ->required()
                                    ->image()
                                    ->maxSize(2048) // 2MB
                                    ->directory('bukti-pembayaran/simpanan')
                                    ->disk('public')
                                    ->visibility('public')
                                    ->columnSpanFull()
                                    ->getUploadedFileNameForStorageUsing(
                                        fn(TemporaryUploadedFile $file): string =>
                                        'simpanan-' . Auth::id() . '-' . time() . '.' . $file->getClientOriginalExtension()
                                    )
                            ]),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Perhatian')
                    ->schema([
                        Forms\Components\Placeholder::make('info')
                            ->label('')
                            ->content('Simpanan Anda akan diverifikasi oleh admin. Silahkan cek status simpanan Anda di halaman ini.'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
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
                // Tables\Actions\ViewAction::make(),
            ])
            ->modifyQueryUsing(function (Builder $query) {
                return $query->where('user_id', Auth::id());
            })
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
            'create' => Pages\CreateSimpanan::route('/create'),
            // 'edit' => Pages\EditSimpanan::route('/{record}/edit'),
            // 'view' => Pages\ViewSimpanan::route('/{record}'),
        ];
    }
}
