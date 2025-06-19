<x-filament-panels::page>
    <div class="space-y-6">
        <div class="p-4 bg-white rounded-lg shadow dark:bg-gray-800">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                Daftar Menjadi Anggota Tetap
            </h3>
            <div class="flex items-center" style="margin: 8px; color: red; ">
                <div class="" style="margin-right: 8px;">
                    <x-heroicon-o-exclamation-circle class="h-6 w-6" style="stroke: red;" />
                </div>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">
                    Untuk menjadi anggota tetap, Anda perlu membayar simpanan pokok sebesar <b>Rp
                        {{ number_format(\App\Models\KoperasiSetting::getSimpananPokokAmount(), 0, ',', '.') }},-</b>
                    dan
                    mengisi
                    data
                    diri lengkap di bawah ini.
                    Setelah diverifikasi oleh admin, Anda akan mendapatkan akses untuk melakukan simpanan wajib dan
                    sukarela.
                </p>
            </div>

            <form wire:submit="submit">
                {{ $this->form }}

                <div class="mt-6 flex justify-end">
                    <x-filament::button type="submit">
                        Daftar Sekarang
                    </x-filament::button>
                </div>
            </form>
        </div>
    </div>
</x-filament-panels::page>
