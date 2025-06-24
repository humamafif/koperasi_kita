<x-filament-panels::page>
    <div class="space-y-6">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
            <div class="p-4 bg-white rounded-lg shadow dark:bg-gray-800">
                <p class="text-sm text-gray-500 dark:text-gray-400">Total Simpanan</p>
                <p class="text-2xl font-bold text-primary-600">Rp {{ number_format($totalSimpanan, 0, ',', '.') }}</p>
            </div>
            <div class="p-4 bg-white rounded-lg shadow dark:bg-gray-800">
                <p class="text-sm text-gray-500 dark:text-gray-400">Total Pinjaman</p>
                <p class="text-2xl font-bold text-info-600">Rp {{ number_format($totalPinjaman, 0, ',', '.') }}</p>
            </div>
            <div class="p-4 bg-white rounded-lg shadow dark:bg-gray-800">
                <p class="text-sm text-gray-500 dark:text-gray-400">Jumlah Transaksi</p>
                <p class="text-2xl font-bold text-success-600">{{ $countTransaksi }}</p>
            </div>
        </div>

        <div class=" bg-white rounded-lg shadow dark:bg-gray-800">

            {{ $this->table }}
        </div>
    </div>
</x-filament-panels::page>
