<div class="bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 p-4 rounded-lg">
    <div class="flex items-center space-x-2">
        <x-heroicon-o-credit-card class="w-6 h-6 text-primary-500" />
        <span class="font-medium">Informasi Rekening Koperasi</span>
    </div>

    <div class="mt-2 text-sm">
        <p class="mb-1">Silahkan transfer pembayaran ke rekening berikut:</p>
        <div class="mt-2 bg-white dark:bg-gray-700 p-3 rounded-md font-medium">
            {{ \App\Models\KoperasiSetting::getRekeningInfoDisplay() }}
        </div>
    </div>

    <div class="mt-3 text-xs text-gray-500 dark:text-gray-400">
        <p>Setelah melakukan pembayaran, mohon upload bukti transfer di bawah ini</p>
    </div>
</div>
