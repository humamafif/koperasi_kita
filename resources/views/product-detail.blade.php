<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $product->nama }} - Koperasi MerahPutih</title>

    <!-- Favicon -->
    <link rel="icon" href="{{ asset('assets/icon.png') }}" type="image/x-icon">

    <!-- Scripts and Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        .navbar-fixed {
            position: fixed;
            top: 0;
            right: 0;
            left: 0;
            z-index: 1030;
            transition: all 0.3s ease;
        }

        /* Custom Red Color */
        .bg-custom-green {
            background-color: #E31E24;
        }

        .text-custom-green {
            color: #E31E24;
        }

        .hover\:bg-custom-green-dark:hover {
            background-color: #c0191e;
            /* Slightly darker shade for hover */
        }

        .hover\:text-custom-green:hover {
            color: #E31E24;
        }

        .bg-custom-green-light {
            background-color: #fce8e9;
            /* Light red for backgrounds */
        }

        .text-custom-green-dark {
            color: #b91c1c;
            /* Darker red for text */
        }

        .hover\:text-custom-green-dark:hover {
            color: #b91c1c;
            /* Darker red for hover */
        }
    </style>
</head>

<body class="antialiased bg-gray-50">
    <!-- Header/Navbar -->
    <header class="navbar-fixed bg-white shadow-sm py-4">
        <nav class="container mx-auto px-4 md:px-8 flex items-center justify-between">
            <div class="flex items-center">
                <a href="/" class="flex items-center">
                    <img src="{{ asset('assets/logo.png') }}" alt="Koperasi MerahPutih Logo" class="h-10 w-auto mr-3">
                </a>
            </div>

            <!-- Desktop Menu -->
            <div class="hidden md:flex items-center space-x-8">
                <a href="/" class="text-gray-800 hover:text-custom-green font-medium">Beranda</a>
                <a href="{{ route('products.index') }}"
                    class="text-gray-800 hover:text-custom-green font-medium">Produk</a>
                <a href="#tentang" class="text-gray-800 hover:text-custom-green font-medium">Tentang Kami</a>
                <a href="#layanan" class="text-gray-800 hover:text-custom-green font-medium">Layanan</a>

                @auth
                    <!-- User sudah login -->
                    <a href="{{ auth()->user()->hasRole('admin') ? url('/admin') : url('/anggota') }}"
                        class="px-4 py-2 bg-custom-green hover:bg-custom-green-dark text-white rounded-md font-medium">
                        Dashboard
                    </a>
                @else
                    <!-- User belum login -->
                    <a href="{{ url('/anggota/login') }}"
                        class="text-gray-800 hover:text-custom-green font-medium">Login</a>
                    <a href="{{ url('/register') }}"
                        class="px-4 py-2 bg-custom-green hover:bg-custom-green-dark text-white rounded-md font-medium">
                        Register
                    </a>
                @endauth
            </div>

            <!-- Mobile menu button -->
            <button id="mobile-menu-button" class="md:hidden flex items-center">
                <svg class="w-6 h-6 text-gray-800" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                    xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16">
                    </path>
                </svg>
            </button>
        </nav>
    </header>

    <!-- Main Content -->
    <main class="py-12 mt-16">
        <div class="container mx-auto px-4">
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- Product Image -->
                    <div class="p-6">
                        @if ($product->gambar)
                            <img src="{{ asset('storage/' . $product->gambar) }}" alt="{{ $product->nama }}"
                                class="w-full h-auto rounded-lg">
                        @else
                            <div class="w-full h-64 bg-gray-200 rounded-lg flex items-center justify-center">
                                <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z">
                                    </path>
                                </svg>
                            </div>
                        @endif
                    </div>

                    <!-- Product Details -->
                    <div class="p-6">
                        <span
                            class="bg-custom-green-light text-custom-green-dark text-xs px-2 py-1 rounded-full">{{ $product->kategori }}</span>
                        <h1 class="text-3xl font-bold text-gray-800 mt-2">{{ $product->nama }}</h1>
                        <div class="mt-4">
                            <span class="text-2xl font-bold text-custom-green">Rp
                                {{ number_format($product->harga, 0, ',', '.') }}</span>
                        </div>

                        <div class="mt-6">
                            <h2 class="text-lg font-semibold text-gray-800">Deskripsi</h2>
                            <p class="mt-2 text-gray-600">
                                {{ \Illuminate\Support\Str::limit(strip_tags($product->deskripsi), 60) }}</p>
                        </div>

                        <div class="mt-6">
                            <div class="flex items-center space-x-2">
                                <span class="text-gray-700 font-medium">Ketersediaan:</span>
                                @if ($product->stok > 0)
                                    <span class="text-custom-green">Stok tersedia ({{ $product->stok }})</span>
                                @else
                                    <span class="text-red-600">Stok habis</span>
                                @endif
                            </div>
                        </div>

                        <div class="mt-8">
                            <a href="{{ route('products.index') }}"
                                class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 mr-4">
                                Kembali ke Daftar Produk
                            </a>

                            @auth
                                <a href="{{ url('/anggota/browse-produk') }}"
                                    class="px-4 py-2 bg-custom-green text-white rounded-md hover:bg-custom-green-dark">
                                    Dashboard Anggota
                                </a>
                            @else
                                <a href="{{ url('/anggota/login') }}"
                                    class="px-4 py-2 bg-custom-green text-white rounded-md hover:bg-custom-green-dark">
                                    Login untuk membeli produk
                                </a>
                            @endauth
                        </div>
                    </div>
                </div>
            </div>

            @if ($relatedProducts->count() > 0)
                <div class="mt-16">
                    <h2 class="text-2xl font-bold mb-6">Produk Terkait</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6">
                        @foreach ($relatedProducts as $related)
                            <div
                                class="bg-white rounded-lg overflow-hidden shadow-md hover:shadow-lg transition duration-300">
                                @if ($related->gambar)
                                    <img src="{{ asset('storage/' . $related->gambar) }}" alt="{{ $related->nama }}"
                                        class="w-full h-40 object-cover">
                                @else
                                    <div class="w-full h-40 bg-gray-200 flex items-center justify-center">
                                        <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z">
                                            </path>
                                        </svg>
                                    </div>
                                @endif
                                <div class="p-4">
                                    <h3 class="text-lg font-semibold mb-2 truncate">{{ $related->nama }}</h3>
                                    <div class="flex justify-between items-center">
                                        <span class="text-custom-green font-bold">Rp
                                            {{ number_format($related->harga, 0, ',', '.') }}</span>
                                        <a href="{{ route('products.show', $related->id) }}"
                                            class="text-sm text-custom-green hover:text-custom-green-dark">Detail</a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-gray-900 text-gray-300 py-10 mt-20">
        <div class="container mx-auto px-4 md:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-10 mb-8">
                <div>
                    <div class="flex items-center mb-4">
                        <img src="{{ asset('assets/logo.png') }}" alt="Koperasi Kita Logo" class="h-10 w-auto mr-3">
                    </div>
                    <p class="mb-4 text-gray-400">Memberikan layanan keuangan terbaik untuk semua anggota dengan
                        prinsip kekeluargaan dan gotong royong.</p>
                </div>
                <div>
                    <h3 class="text-lg font-semibold mb-4 text-white">Tautan Cepat</h3>
                    <ul class="space-y-3">
                        <li><a href="/"
                                class="text-gray-400 hover:text-custom-green transition duration-300">Beranda</a></li>
                        <li><a href="{{ route('products.index') }}"
                                class="text-gray-400 hover:text-custom-green transition duration-300">Produk</a></li>
                        <li><a href="/#tentang"
                                class="text-gray-400 hover:text-custom-green transition duration-300">Tentang Kami</a>
                        </li>
                        <li><a href="/#layanan"
                                class="text-gray-400 hover:text-custom-green transition duration-300">Layanan</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-lg font-semibold mb-4 text-white">Kontak</h3>
                    <ul class="space-y-3">
                        <li class="flex items-start">
                            <svg class="w-5 h-5 text-custom-green mt-1 mr-3" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z">
                                </path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            <span class="text-gray-400">Jl. Koperasi No. 123, Kota Anda</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="w-5 h-5 text-custom-green mt-1 mr-3" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z">
                                </path>
                            </svg>
                            <span class="text-gray-400">info@koperasikita.com</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="w-5 h-5 text-custom-green mt-1 mr-3" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z">
                                </path>
                            </svg>
                            <span class="text-gray-400">(021) 123-4567</span>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-gray-700 pt-6 flex flex-col md:flex-row justify-between items-center">
                <p class="text-gray-400">© {{ date('Y') }} Koperasi Kita. Hak Cipta Dilindungi.</p>
                <div class="flex space-x-6 mt-4 md:mt-0">
                    <a href="#" class="text-gray-400 hover:text-custom-green transition duration-300">Syarat &
                        Ketentuan</a>
                    <a href="#" class="text-gray-400 hover:text-custom-green transition duration-300">Kebijakan
                        Privasi</a>
                </div>
            </div>
        </div>
    </footer>
</body>

</html>
