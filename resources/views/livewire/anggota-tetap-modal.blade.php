<div>
    @if ($showModal)
        <div x-data="{ show: true }" x-show="show" x-cloak x-transition
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 max-w-md w-full mx-4">
                <h2 class="text-lg font-bold mb-4 dark:text-white">Daftar Anggota Tetap</h2>

                <p class="mb-4 dark:text-gray-300">
                    Untuk mengakses halaman simpanan, Anda perlu menjadi anggota tetap terlebih dahulu.
                    Silahkan daftar sebagai anggota tetap dengan membayar simpanan pokok.
                </p>

                <div class="flex justify-end space-x-2">
                    <button x-on:click="show = false" type="button"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 dark:bg-gray-700 dark:text-white dark:border-gray-600 dark:hover:bg-gray-600">
                        Tutup
                    </button>

                    <a href="{{ route('filament.anggota.pages.daftar-anggota-tetap') }}"
                        class="px-4 py-2 text-sm font-medium text-white bg-primary-600 border border-transparent rounded-md shadow-sm hover:bg-primary-700">
                        Daftar Sekarang
                    </a>
                </div>
            </div>
        </div>
    @endif
</div>
