@php
    $pendingRegistration = $this->getPendingRegistration();
@endphp

<x-filament::section>
    <div class="space-y-4">
        @if ($pendingRegistration)
            <!-- Widget untuk pendaftaran yang sedang pending -->
            <div class="flex items-center gap-4">
                <div class="rounded-full bg-amber-50 p-3">
                    <x-heroicon-o-clock class="h-6 w-6 text-amber-500" style="color: blue;" />
                </div>
                <div>
                    <h2 class="text-lg font-medium">Pendaftaran Anggota Tetap Sedang Diproses</h2>
                    <p class="text-sm text-gray-500">Pendaftaran Anda sedang ditinjau oleh administrator. Mohon tunggu
                        konfirmasi selanjutnya.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4">
                <div class="bg-amber-50 p-4 rounded-lg border border-amber-200">
                    <h3 class="font-medium mb-2 text-amber-800">Informasi Pendaftaran:</h3>
                    <ul class="space-y-2 text-sm text-gray-600">
                        <li class="flex justify-between">
                            <span class="text-gray-500">Tanggal Pengajuan:</span>
                            <span
                                class="font-medium">{{ $pendingRegistration->tanggal_pengajuan->format('d M Y') }}</span>
                        </li>
                        <li class="flex justify-between">
                            <span class="text-gray-500">Status:</span>
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800">
                                Menunggu Persetujuan
                            </span>
                        </li>
                    </ul>

                    <div class="mt-4 text-sm text-amber-700">
                        <p>Kami akan memberitahu Anda segera setelah pendaftaran Anda disetujui. Setelah disetujui, Anda
                            dapat mengakses semua fitur koperasi termasuk Simpan dan Pinjam.</p>
                    </div>
                </div>
            </div>
        @else
            <!-- Widget untuk ajakan mendaftar -->
            <div class="flex items-center gap-4">
                <div class="rounded-full bg-primary-50 p-3">
                    <x-heroicon-o-arrow-up-circle class="h-6 w-6 text-primary-500" />
                </div>
                <div>
                    <h2 class="text-lg font-medium">Ingin Mengakses Fitur Simpan Pinjam?</h2>
                    <p class="text-sm text-gray-500">Upgrade menjadi anggota tetap untuk akses ke fitur simpan pinjam
                        dan manfaat eksklusif lainnya.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div class="p-4 rounded-lg border border-gray-200">
                    <h3 class="font-medium mb-2">Manfaat Anggota Tetap:</h3>
                    <ul class="list-disc list-inside space-y-1 text-sm text-gray-400">
                        <li>Akses ke fitur simpan dan pinjam</li>
                        <li>Bunga pinjaman yang kompetitif</li>
                        <li>Bagi hasil simpanan yang menarik</li>
                        <li>Layanan prioritas anggota</li>
                    </ul>
                </div>

                <div class="bg-primary-50 p-4 rounded-lg flex flex-col justify-center">
                    <p class="text-sm text-primary-700 mb-4">Proses upgrade cepat dan mudah. Lengkapi data dan bayar
                        simpanan pokok untuk mulai menikmati manfaat.</p>
                    <a href="{{ route('filament.anggota.pages.daftar-anggota-tetap') }}"
                        class="filament-button filament-button-size-md inline-flex items-center justify-center py-1 gap-1 font-medium rounded-lg border transition-colors focus:outline-none focus:ring-offset-2 focus:ring-2 focus:ring-inset filament-button-color-primary bg-primary-600 hover:bg-primary-500 focus:ring-primary-500 focus:bg-primary-500 focus:ring-offset-primary-700 text-white border-transparent">
                        Daftar Sekarang
                    </a>
                </div>
            </div>
        @endif
    </div>
</x-filament::section>
