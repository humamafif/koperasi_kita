<?php

namespace App\Filament\Anggota\Pages;

use App\Models\PendaftaranAnggotaTetap;
use App\Models\Simpanan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class DaftarAnggotaTetap extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-user-plus';
    protected static ?string $navigationLabel = 'Daftar Anggota Tetap';
    protected static ?string $title = 'Pendaftaran Anggota Tetap';
    protected static ?string $slug = 'daftar-anggota-tetap';
    protected static ?int $navigationSort = 3;


    protected static string $view = 'filament.anggota.pages.daftar-anggota-tetap';

    public ?array $data = [];
    public ?int $simpananPokok = null;
    protected $shouldShowPage = true;

    public static function shouldRegisterNavigation(): bool
    {
        if (Auth::user()->hasRole('anggota_tetap') || Auth::user()->is_anggota_tetap) {
            return false;
        }
        $pendingRegistration = PendaftaranAnggotaTetap::where('user_id', Auth::id())
            ->where('status', 'pending')
            ->exists();
        if ($pendingRegistration) {
            return false;
        }

        return true;
    }

    public function mount(): void
    {
        // Dapatkan nilai simpanan pokok
        $this->simpananPokok = \App\Models\KoperasiSetting::getSimpananPokokAmount();
        // Cek jika user sudah anggota tetap atau memiliki pendaftaran yang pending
        if (Auth::user()->hasRole('anggota_tetap') || Auth::user()->is_anggota_tetap) {
            Notification::make()
                ->title('Anda Sudah Menjadi Anggota Tetap')
                ->body('Anda sudah terdaftar sebagai anggota tetap.')
                ->success()
                ->send();

            $this->redirect(route('filament.anggota.pages.dashboard'));
        }

        $pendingRegistration = PendaftaranAnggotaTetap::where('user_id', Auth::id())
            ->where('status', 'pending')
            ->first();

        if ($pendingRegistration) {
            Notification::make()
                ->title('Pendaftaran Sedang Diproses')
                ->body('Pendaftaran Anda sedang dalam proses peninjauan.')
                ->warning()
                ->send();

            $this->redirect(route('filament.anggota.pages.dashboard'));
        }
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\TextInput::make('nik')
                            ->label('NIK')
                            ->required()
                            ->maxLength(16)
                            ->minLength(16)
                            ->numeric(),

                        Forms\Components\TextInput::make('no_telepon')
                            ->label('Nomor Telepon')
                            ->required()
                            ->tel()
                            ->maxLength(15),

                        Forms\Components\Textarea::make('alamat')
                            ->label('Alamat Lengkap')
                            ->required()
                            ->rows(3)
                            ->columnSpanFull(),
                        Forms\Components\Section::make('Pembayaran Tagihan')->schema([
                            Forms\Components\View::make('filament.components.rekening-info')
                                ->label('Informasi Rekening')
                                ->columnSpanFull(),
                            Forms\Components\FileUpload::make('bukti_pembayaran')
                                ->label('Bukti Pembayaran Simpanan Pokok')
                                ->required()
                                ->image()
                                ->maxSize(2048)
                                ->directory('bukti-pembayaran/simpanan-pokok')
                                ->disk('public')
                                ->visibility('public')
                                ->getUploadedFileNameForStorageUsing(
                                    fn(TemporaryUploadedFile $file): string =>
                                    'simpanan-pokok-' . Auth::id() . '-' . time() . '.' . $file->getClientOriginalExtension()
                                )
                        ]),
                    ]),

                Forms\Components\Section::make('Pernyataan')
                    ->schema([
                        Forms\Components\Checkbox::make('persetujuan')
                            ->label(function () {
                                $simpananPokok = \App\Models\KoperasiSetting::getSimpananPokokAmount();
                                return "Saya menyatakan bahwa data yang saya isi adalah benar dan bersedia menjadi anggota tetap Koperasi Kita dengan membayar simpanan pokok sebesar Rp " . number_format($simpananPokok, 0, ',', '.') . ",-";
                            })
                            ->required()
                            ->columnSpanFull(),
                    ]),
            ])
            ->statePath('data');
    }

    public function submit(): void
    {
        $data = $this->form->getState();

        // Validasi persetujuan
        if (!isset($data['persetujuan']) || !$data['persetujuan']) {
            Notification::make()
                ->title('Persetujuan Diperlukan')
                ->body('Anda harus menyetujui pernyataan untuk melanjutkan pendaftaran.')
                ->danger()
                ->send();
            return;
        }
        // Validasi NIK - cek apakah sudah digunakan oleh anggota lain
        $nikExists = PendaftaranAnggotaTetap::where('nik', $data['nik'])
            ->where('user_id', '!=', Auth::id())
            ->where(function ($query) {
                $query->where('status', 'disetujui')
                    ->orWhere('status', 'pending');
            })
            ->exists();

        if ($nikExists) {
            Notification::make()
                ->title('NIK Sudah Terdaftar')
                ->body('NIK yang Anda masukkan sudah terdaftar di sistem. Silakan gunakan NIK lain atau hubungi administrator.')
                ->danger()
                ->send();
            return;
        }

        // Validasi nomor telepon - cek apakah sudah digunakan oleh anggota lain
        $phoneExists = PendaftaranAnggotaTetap::where('no_telepon', $data['no_telepon'])
            ->where('user_id', '!=', Auth::id())
            ->where(function ($query) {
                $query->where('status', 'disetujui')
                    ->orWhere('status', 'pending');
            })
            ->exists();

        // Cek juga di tabel users
        $phoneExistsInUsers = \App\Models\User::where('no_telepon', $data['no_telepon'])
            ->where('id', '!=', Auth::id())
            ->where('is_anggota_tetap', true)
            ->exists();

        if ($phoneExists || $phoneExistsInUsers) {
            Notification::make()
                ->title('Nomor Telepon Sudah Terdaftar')
                ->body('Nomor telepon yang Anda masukkan sudah terdaftar di sistem. Silakan gunakan nomor telepon lain.')
                ->danger()
                ->send();
            return;
        }
        $simpananPokok = \App\Models\KoperasiSetting::getSimpananPokokAmount();
        // Buat pendaftaran anggota tetap
        $pendaftaran = PendaftaranAnggotaTetap::create([
            'user_id' => Auth::id(),
            'nik' => $data['nik'],
            'alamat' => $data['alamat'],
            'no_telepon' => $data['no_telepon'],
            'bukti_pembayaran' => $data['bukti_pembayaran'],
            'status' => 'pending',
            'tanggal_pengajuan' => now(),
        ]);

        // Buat simpanan pokok
        Simpanan::create([
            'user_id' => Auth::id(),
            'jenis' => 'pokok',
            'jumlah' => $simpananPokok,
            'bukti_pembayaran' => $data['bukti_pembayaran'],
            'status' => 'pending',
            'tanggal_pembayaran' => now(),
        ]);

        Notification::make()
            ->title('Pendaftaran Berhasil')
            ->body('Pendaftaran anggota tetap berhasil diajukan. Silahkan tunggu konfirmasi dari admin.')
            ->success()
            ->send();

        $this->redirect('/anggota');
    }

    protected function getFormActions(): array
    {
        return [
            Forms\Components\Actions\Action::make('submit')
                ->label('Daftar Sekarang')
                ->submit('submit'),
        ];
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        if (!$this->shouldShowPage) {
            return view('filament.anggota.pages.redirecting');
        }
        return parent::render();
    }
}
