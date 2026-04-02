<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Koperasi MerahPutih - Solusi Finansial Bersama</title>

    <!-- Favicon -->
    <link rel="icon" href="{{ asset('assets/icon.png') }}" type="image/x-icon">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Scripts and Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        html {
            scroll-behavior: smooth;
        }

        .navbar-fixed {
            position: fixed;
            top: 0;
            right: 0;
            left: 0;
            z-index: 1030;
            transition: all 0.3s ease;
        }

        .navbar-scrolled {
            background-color: #ffffff;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }

        .section-padding {
            padding-top: 6rem;
        }

        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }

        /* Custom Red Color */
        .bg-custom-green {
            background-color: #E31E24;
        }

        .bg-custom-green:hover {
            background-color: #c0191e;
        }

        .text-custom-green {
            color: #E31E24;
        }

        .hover\:text-custom-green:hover {
            color: #E31E24;
        }

        .border-custom-green {
            border-color: #E31E24;
        }

        .bg-custom-green-light {
            background-color: rgba(227, 30, 36, 0.1);
        }
    </style>
</head>

<body class="antialiased bg-white">
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
                        class="px-4 py-2 bg-custom-green hover:bg-custom-green text-white rounded-md font-medium">
                        Dashboard
                    </a>
                @else
                    <!-- User belum login -->
                    <a href="{{ url('/anggota/login') }}"
                        class="text-gray-800 hover:text-custom-green font-medium">Login</a>
                    <a href="{{ url('/register') }}"
                        class="px-4 py-2 bg-custom-green hover:bg-custom-green text-white rounded-md font-medium">
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

        <!-- Mobile Menu -->
        <div id="mobile-menu" class="hidden md:hidden bg-white w-full border-t border-gray-200 mt-3">
            <div class="container mx-auto px-4 py-3 flex flex-col space-y-3">
                <a href="/" class="text-gray-800 hover:text-custom-green font-medium py-2">Beranda</a>
                <a href="{{ route('products.index') }}"
                    class="text-gray-800 hover:text-custom-green font-medium py-2">Produk</a>
                <a href="#tentang" class="text-gray-800 hover:text-custom-green font-medium py-2">Tentang Kami</a>
                <a href="#layanan" class="text-gray-800 hover:text-custom-green font-medium py-2">Layanan</a>

                @auth
                    <!-- User sudah login -->
                    <a href="{{ auth()->user()->hasRole('admin') ? url('/admin') : url('/anggota') }}"
                        class="inline-block px-4 py-2 bg-custom-green hover:bg-custom-green text-white rounded-md font-medium mt-2">
                        Dashboard
                    </a>
                @else
                    <!-- User belum login -->
                    <a href="{{ url('/anggota/login') }}"
                        class="text-gray-800 hover:text-custom-green font-medium py-2">Login</a>
                    <a href="{{ url('/register') }}"
                        class="inline-block px-4 py-2 bg-custom-green hover:bg-custom-green text-white rounded-md font-medium mt-2">
                        Register
                    </a>
                @endauth
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="relative h-screen overflow-hidden">
        <!-- Background image with gradient overlay -->
        <div class="absolute inset-0 z-0">
            <img src="{{ asset('assets/bg-hero-section.jpg') }}" alt="Background Hero"
                class="w-full h-full object-[position:center] object-none">
            <div class="absolute inset-0 bg-gradient-to-r from-black/50 to-transparent"></div>
        </div>

        <!-- Content -->
        <div class="container mx-auto px-4 md:px-8 h-full flex items-center relative z-10">
            <div class="max-w-2xl text-white">
                <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold mb-6 leading-tight">
                    Solusi Finansial yang Tepercaya untuk Anggota
                </h1>
                <p class="text-lg md:text-xl mb-8">
                    <span class="text-2xl">Koperasi</span><span class="font-bold text-2xl" style="color: #E31E24;">
                        Merah</span><span class="font-bold text-2xl" style="color: #ffadb0;">Putih</span>
                    <span class="font-light">membantu Anda
                        mengelola
                        keuangan,
                        menyediakan
                        layanan simpan pinjam, dan berbagai produk kebutuhan sehari-hari.</span>
                </p>
                <div class="flex flex-wrap gap-4">
                    @auth
                        <a href="{{ auth()->user()->hasRole('admin') ? url('/admin') : url('/anggota') }}"
                            class="px-6 py-3 bg-white text-custom-green hover:bg-gray-100 rounded-md font-medium transition duration-300">
                            Dashboard Saya
                        </a>
                    @else
                        <a href="{{ url('/register') }}"
                            class="px-6 py-3 bg-white text-custom-green hover:bg-gray-100 rounded-md font-medium transition duration-300">
                            Daftar Sekarang
                        </a>
                    @endauth
                    <a href="#layanan"
                        class="px-6 py-3 bg-transparent border border-white hover:bg-white/10 rounded-md font-medium transition duration-300">
                        Pelajari Layanan
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="layanan" class="py-20 bg-gray-50 section-padding">
        <div class="container mx-auto px-4 md:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold mb-4 text-gray-800">Layanan Kami</h2>
                <div class="w-20 h-1 bg-custom-green mx-auto mb-4"></div>
                <p class="text-gray-600 max-w-2xl mx-auto">Kami menyediakan berbagai layanan finansial untuk memenuhi
                    kebutuhan anggota koperasi dengan proses yang mudah dan cepat.</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Feature 1 -->
                <div
                    class="p-6 border border-gray-200 rounded-lg shadow-sm hover:shadow-md transition duration-300 feature-card bg-white">
                    <div class="w-14 h-14 bg-custom-green-light rounded-full flex items-center justify-center mb-5">
                        <svg class="w-7 h-7 text-custom-green" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                            </path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold mb-3 text-gray-800">Simpanan</h3>
                    <p class="text-gray-600">Layanan simpanan dengan bunga kompetitif dan berbagai
                        pilihan jangka waktu sesuai kebutuhan Anda. Nikmati kemudahan menabung dan mengakses dana kapan
                        saja.</p>
                </div>

                <!-- Feature 2 -->
                <div
                    class="p-6 border border-gray-200 rounded-lg shadow-sm hover:shadow-md transition duration-300 feature-card bg-white">
                    <div class="w-14 h-14 bg-custom-green-light rounded-full flex items-center justify-center mb-5">
                        <svg class="w-7 h-7 text-custom-green" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z">
                            </path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold mb-3 text-gray-800">Pinjaman</h3>
                    <p class="text-gray-600">Pinjaman dengan bunga rendah, proses cepat, dan
                        persyaratan yang mudah untuk membantu kebutuhan finansial Anda. Kami menawarkan fleksibilitas
                        pembayaran.</p>
                </div>

                <!-- Feature 3 -->
                <div
                    class="p-6 border border-gray-200 rounded-lg shadow-sm hover:shadow-md transition duration-300 feature-card bg-white">
                    <div class="w-14 h-14 bg-custom-green-light rounded-full flex items-center justify-center mb-5">
                        <svg class="w-7 h-7 text-custom-green" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold mb-3 text-gray-800">Produk Koperasi</h3>
                    <p class="text-gray-600">Berbagai produk kebutuhan sehari-hari dengan harga
                        terjangkau dan kualitas terbaik untuk anggota. Kami secara rutin memperbarui koleksi produk
                        kami.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="tentang" class="py-20 bg-white section-padding">
        <div class="container mx-auto px-4 md:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-12 items-center">
                <div class="order-2 md:order-1">
                    <div class="mb-4">
                        <span class="text-custom-green font-semibold">Tentang Kami</span>
                    </div>
                    <h2 class="text-3xl md:text-4xl font-bold mb-6 text-gray-800">Tentang Koperasi Kita</h2>
                    <p class="mb-4 text-gray-600">Koperasi Kita didirikan dengan tujuan untuk
                        membantu anggota dalam memenuhi kebutuhan ekonomi dan sosial melalui kerja sama yang saling
                        menguntungkan.</p>
                    <p class="mb-8 text-gray-600">Dengan prinsip kekeluargaan dan gotong royong, kami
                        berkomitmen untuk memberikan layanan terbaik kepada seluruh anggota koperasi.</p>
                    <ul class="space-y-4">
                        <li class="flex items-center">
                            <div
                                class="w-8 h-8 bg-custom-green-light rounded-full flex items-center justify-center mr-3">
                                <svg class="w-4 h-4 text-custom-green" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7"></path>
                                </svg>
                            </div>
                            <span class="text-gray-700">Didirikan tahun 2022</span>
                        </li>
                        <li class="flex items-center">
                            <div
                                class="w-8 h-8 bg-custom-green-light rounded-full flex items-center justify-center mr-3">
                                <svg class="w-4 h-4 text-custom-green" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7"></path>
                                </svg>
                            </div>
                            <span class="text-gray-700">Lebih dari 500 anggota aktif</span>
                        </li>
                        <li class="flex items-center">
                            <div
                                class="w-8 h-8 bg-custom-green-light rounded-full flex items-center justify-center mr-3">
                                <svg class="w-4 h-4 text-custom-green" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7"></path>
                                </svg>
                            </div>
                            <span class="text-gray-700">Beragam layanan finansial dan produk</span>
                        </li>
                    </ul>
                </div>
                <div class="order-1 md:order-2">
                    <img src="{{ asset('assets/about.jpg') }}" alt="Tentang Koperasi Kita"
                        class="w-full h-auto rounded-xl shadow-lg object-cover">
                </div>
            </div>
        </div>
    </section>

    <!-- Produk Terbaru Section -->
    <section id="produk" class="py-20 section-padding">
        <div class="container mx-auto px-4 md:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold mb-4 text-gray-800">Produk Terbaru</h2>
                <div class="w-20 h-1 bg-custom-green mx-auto mb-4"></div>
                <p class="text-gray-600 max-w-2xl mx-auto">Berbagai produk kebutuhan sehari-hari dengan harga
                    terjangkau
                    dan kualitas terbaik untuk anggota koperasi.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-3 gap-8">
                @foreach ($products as $product)
                    <div
                        class="bg-white rounded-lg shadow-md overflow-hidden transition-transform duration-300 hover:shadow-xl hover:-translate-y-1">
                        <a href="{{ route('products.show', $product->id) }}">
                            <div class="h-48 overflow-hidden">
                                @if ($product->gambar)
                                    <img src="{{ asset('storage/' . $product->gambar) }}" alt="{{ $product->nama }}"
                                        class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full flex items-center justify-center bg-gray-200">
                                        <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z">
                                            </path>
                                        </svg>
                                    </div>
                                @endif
                            </div>
                        </a>
                        <div class="p-4">
                            <span
                                class="text-xs font-semibold bg-custom-green-light text-custom-green px-2 py-1 rounded-full">{{ $product->kategori }}</span>
                            <h3 class="mt-2 text-lg font-semibold text-gray-800 hover:text-custom-green truncate">
                                <a href="{{ route('products.show', $product->id) }}">{{ $product->nama }}</a>
                            </h3>
                            <p class="mt-1 text-gray-600 h-12 overflow-hidden text-sm">
                                {{ \Illuminate\Support\Str::limit(strip_tags($product->deskripsi), 60) }}
                            </p>
                            <div class="mt-4 flex justify-between items-center">
                                <span class="text-lg font-bold text-custom-green">Rp
                                    {{ number_format($product->harga, 0, ',', '.') }}</span>
                                <span class="text-sm text-gray-500">Stok: {{ $product->stok }}</span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-12 text-center">
                <a href="{{ route('products.index') }}"
                    class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-custom-green hover:bg-custom-green">
                    Lihat Semua Produk
                    <svg class="ml-2 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                    </svg>
                </a>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-20 bg-custom-green text-white">
        <div class="container mx-auto px-4 md:px-8 text-center">
            <h2 class="text-3xl md:text-4xl font-bold mb-4">Bergabung dengan kami</h2>
            <p class="text-xl mb-10 max-w-2xl mx-auto opacity-90">
                @auth
                    Gunakan dashboard untuk mengakses semua fitur dan layanan kami.
                @else
                    Nikmati berbagai keuntungan dan kemudahan dalam mengelola keuangan Anda.
                    Daftar sekarang dan menjadi bagian dari keluarga Koperasi Kita!
                @endauth
            </p>

            @auth
                <a href="{{ auth()->user()->hasRole('admin') ? url('/admin') : url('/anggota') }}"
                    class="inline-block px-8 py-4 bg-white text-custom-green hover:bg-gray-100 rounded-md font-medium text-lg transition duration-300 shadow-md hover:shadow-lg">
                    Dashboard Saya
                </a>
            @else
                <a href="{{ url('/register') }}"
                    class="inline-block px-8 py-4 bg-white text-custom-green hover:bg-gray-100 rounded-md font-medium text-lg transition duration-300 shadow-md hover:shadow-lg">
                    Daftar Sekarang
                </a>
            @endauth
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-900 text-gray-300 py-10">
        <div class="container mx-auto px-4 md:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-10 mb-8">
                <div>
                    <div class="flex items-center mb-4">
                        <img src="{{ asset('assets/logo.png') }}" alt="Koperasi Kita Logo" class="h-10 w-auto mr-3">
                    </div>
                    <p class="mb-4 text-gray-400">Memberikan layanan keuangan terbaik untuk semua anggota dengan
                        prinsip
                        kekeluargaan dan gotong royong.</p>
                </div>
                <div>
                    <h3 class="text-lg font-semibold mb-4 text-white">Tautan Cepat</h3>
                    <ul class="space-y-3">
                        <li><a href="/"
                                class="text-gray-400 hover:text-custom-green transition duration-300">Beranda</a></li>
                        <li><a href="{{ route('products.index') }}"
                                class="text-gray-400 hover:text-custom-green transition duration-300">Produk</a></li>
                        <li><a href="#tentang"
                                class="text-gray-400 hover:text-custom-green transition duration-300">Tentang Kami</a>
                        </li>
                        <li><a href="#layanan"
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
                <p class="text-gray-400">&copy; {{ date('Y') }} Koperasi Kita. Hak Cipta Dilindungi.</p>
                <div class="flex space-x-6 mt-4 md:mt-0">
                    <a href="#" class="text-gray-400 hover:text-custom-green transition duration-300">Syarat &
                        Ketentuan</a>
                    <a href="#" class="text-gray-400 hover:text-custom-green transition duration-300">Kebijakan
                        Privasi</a>
                </div>
            </div>
        </div>
    </footer>

    <script>
        // Handle mobile menu toggle
        const mobileMenuButton = document.getElementById('mobile-menu-button');
        const mobileMenu = document.getElementById('mobile-menu');

        mobileMenuButton.addEventListener('click', () => {
            mobileMenu.classList.toggle('hidden');
        });

        // Navbar background change on scroll
        const navbar = document.querySelector('header');

        window.addEventListener('scroll', () => {
            if (window.scrollY > 50) {
                navbar.classList.add('navbar-scrolled');
            } else {
                navbar.classList.remove('navbar-scrolled');
            }
        });

        // Handle smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();

                const href = this.getAttribute('href');

                if (href === '#') {
                    window.scrollTo({
                        top: 0,
                        behavior: 'smooth'
                    });
                } else {
                    const target = document.querySelector(href);
                    if (target) {
                        target.scrollIntoView({
                            behavior: 'smooth'
                        });
                    }
                }

                // Close mobile menu if open
                if (!mobileMenu.classList.contains('hidden')) {
                    mobileMenu.classList.add('hidden');
                }
            });
        });
    </script>
</body>

</html>
