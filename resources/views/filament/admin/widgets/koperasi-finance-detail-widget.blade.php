<x-filament-widgets::widget>
    <x-filament::section>
        <div class="space-y-6">
            <h2 class="text-xl font-bold tracking-tight">
                Detail Keuangan Koperasi
            </h2>

            <div class="rounded-lg  border-gray-200 dark:border-gray-700 ">
                <!-- Total Saldo Koperasi -->
                <div class="mb-6" style="margin-bottom: 18px">
                    <div class="flex justify-between items-center">
                        <h3 class="text-xl font-semibold">Total Saldo Koperasi</h3>
                        <div class="text-2xl font-bold text-primary-600 dark:text-primary-500">
                            Rp {{ number_format($this->getTotalSaldoKoperasi(), 0, ',', '.') }}
                        </div>
                    </div>
                </div>

                <!-- Layout 2x3 Grid untuk Detail Keuangan -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <!-- Simpanan Pokok -->
                    <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-4">
                        <div class="flex flex-col">
                            <div class="font-medium text-gray-500 dark:text-gray-400 text-sm">Simpanan Pokok</div>
                            <div class="text-lg font-semibold mt-1">
                                Rp {{ number_format($this->getTotalSimpananPokokData()['jumlah'], 0, ',', '.') }}
                            </div>
                            <div class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                                {{ $this->getTotalSimpananPokokData()['anggota_count'] }} anggota
                            </div>
                        </div>
                    </div>

                    <!-- Simpanan Wajib -->
                    <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-4 ">
                        <div class="flex flex-col">
                            <div class="font-medium text-gray-500 dark:text-gray-400 text-sm">Simpanan Wajib</div>
                            <div class="text-lg font-semibold text-success-600 dark:text-success-500 mt-1">
                                Rp {{ number_format($this->getTotalSimpananWajibData()['jumlah'], 0, ',', '.') }}
                            </div>
                            <div class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                                {{ $this->getTotalSimpananWajibData()['anggota_count'] }} anggota |
                                {{ $this->getTotalSimpananWajibData()['transaksi_count'] }} transaksi
                            </div>
                        </div>
                    </div>

                    <!-- Simpanan Sukarela -->
                    <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-4 ">
                        <div class="flex flex-col">
                            <div class="font-medium text-gray-500 dark:text-gray-400 text-sm">Simpanan Sukarela</div>
                            <div class="text-lg font-semibold text-info-600 dark:text-info-500 mt-1">
                                Rp {{ number_format($this->getTotalSimpananSukarelaData()['jumlah'], 0, ',', '.') }}
                            </div>
                            <div class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                                {{ $this->getTotalSimpananSukarelaData()['anggota_count'] }} anggota |
                                {{ $this->getTotalSimpananSukarelaData()['transaksi_count'] }} transaksi
                            </div>
                        </div>
                    </div>

                    <!-- Biaya Admin -->
                    <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-4">
                        <div class="flex flex-col">
                            <div class="font-medium text-gray-500 dark:text-gray-400 text-sm">Biaya Admin (1.5%)</div>
                            <div class="text-lg font-semibold text-warning-600 dark:text-warning-500 mt-1">
                                Rp {{ number_format($this->getBiayaAdminData()['jumlah'], 0, ',', '.') }}
                            </div>
                            <div class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                                {{ $this->getBiayaAdminData()['transaksi_count'] }} transaksi produk
                            </div>
                        </div>
                    </div>

                    <!-- Bunga Pinjaman -->
                    <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-4 ">
                        <div class="flex flex-col">
                            <div class="font-medium text-gray-500 dark:text-gray-400 text-sm">Pinjaman yang sudah di
                                bayar</div>
                            <div class="flex items-center space-x-2 mt-1">
                                <span class="text-lg font-semibold text-success-600 dark:text-success-500">
                                    Rp {{ number_format($this->getPinjamanYangSudahDibayar(), 0, ',', '.') }}
                                </span>
                                <span style="margin-left: 8px;"
                                    class="text-sm font-medium text-gray-600 dark:text-gray-400">
                                    (Bunga: Rp
                                    {{ number_format($this->getBungaPinjamanData()['jumlah'], 0, ',', '.') }})
                                </span>
                            </div>
                            <div class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                                {{ $this->getBungaPinjamanData()['pinjaman_count'] }} pinjaman |
                                {{ $this->getBungaPinjamanData()['transaksi_count'] }} pembayaran
                            </div>
                        </div>
                    </div>
                    <!-- Anggota Tetap -->
                    <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-4 ">
                        <div class="flex flex-col">
                            <div class="font-medium text-gray-500 dark:text-gray-400 text-sm">Anggota Tetap</div>
                            <div class="text-lg font-semibold text-success-600 dark:text-success-500 mt-1">
                                {{ $this->getAnggotaTetapCount() }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer information -->
                {{-- <div class="mt-4 text-xs text-gray-500 dark:text-gray-400 text-right">
                    Total anggota tetap: {{ $this->getAnggotaTetapCount() }}
                </div> --}}
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
