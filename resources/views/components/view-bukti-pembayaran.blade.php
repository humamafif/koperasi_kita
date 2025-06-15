@php
    $image = $getRecord()?->bukti_pembayaran ? Storage::disk('public')->url($getRecord()->bukti_pembayaran) : null;
@endphp

@if ($image)
    <div x-data="{ open: false }" class="text-center">
        <!-- Gambar Thumbnail -->
        <img src="{{ $image }}" alt="Bukti Pembayaran" @click="open = true"
            class="w-32 h-auto mx-auto rounded-md shadow cursor-pointer transition hover:scale-105" />

        <!-- Modal Gambar -->
        <div x-show="open" x-transition.opacity x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/80" @click.self="open = false"
            @keydown.escape.window="open = false">
            <div class="relative max-w-3xl w-full p-4">
                <!-- Tombol Close -->
                <button @click="open = false"
                    class="absolute top-2 right-2 text-red text-2xl font-bold bg-black/40 rounded-full w-10 h-10 flex items-center justify-center hover:bg-black/60 transition"
                    aria-label="Tutup">
                    &times;
                </button>

                <!-- Gambar -->
                <img src="{{ $image }}" alt="Bukti Pembayaran"
                    class="max-w-full max-h-[80vh] mx-auto rounded-lg shadow-2xl object-contain">
            </div>
        </div>
    </div>
@else
    <p class="text-sm text-gray-500 text-center">Belum ada bukti pembayaran.</p>
@endif
