<p align="center"><img src="public/assets/logo.png" width="400" alt="Koperasi Kita Logo"></p>

# Koperasi Kita - Platform Koperasi Digital

Koperasi Kita adalah platform koperasi digital yang dirancang untuk memudahkan pengelolaan koperasi, mulai dari keanggotaan, simpan pinjam, hingga marketplace produk anggota. Aplikasi ini memberikan kemudahan akses layanan koperasi secara online dan transparan.

## Fitur Utama

### 1. Manajemen Keanggotaan
- **Pendaftaran Anggota Baru**: Proses pendaftaran online dengan verifikasi data
- **Upgrade Keanggotaan**: Kemudahan upgrade status dari anggota biasa menjadi anggota tetap
- **Dashboard Anggota**: Informasi saldo, transaksi, dan status keanggotaan secara real-time

### 2. Sistem Simpanan
- **Simpanan Pokok**: Setoran awal untuk menjadi anggota tetap
- **Simpanan Wajib**: Pembayaran simpanan rutin bulanan
- **Simpanan Sukarela**: Fleksibilitas menambah simpanan kapan sajaS
### 3. Layanan Pinjaman
- **Pengajuan Pinjaman Online**: Proses pengajuan cepat dan mudah
- **Perhitungan Angsuran Otomatis**: Simulasi angsuran sesuai tenor yang dipilih
- **Tagihan Bulanan**: Reminder tagihan jatuh tempo
- **Pembayaran Fleksibel**: Upload bukti pembayaran untuk verifikasi

### 4. Marketplace Produk Anggota
- **Katalog Produk**: Tampilan produk yang dijual oleh sesama anggota
- **Sistem Pembelian**: Proses pembelian dengan status pembelian real-time
- **Biaya Admin Transparan**: Perhitungan biaya admin otomatis dengan persentase yang jelas
- **Tracking Status**: Pemantauan status pesanan dari proses hingga selesai

### 5. Distribusi SHU (Sisa Hasil Usaha)
- **Perhitungan Otomatis**: Sistem kalkulasi SHU berdasarkan kontribusi anggota
- **Distribusi Proporsional**: Pembagian berdasarkan simpanan, transaksi, dan pinjaman
- **Transparansi**: Detail perhitungan yang jelas untuk setiap anggota
- **Klaim SHU**: Proses pencairan SHU yang mudah

### 6. Panel Administrasi
- **Verifikasi Anggota**: Proses persetujuan keanggotaan
- **Pengelolaan Simpanan & Pinjaman**: Validasi dan pencairan
- **Perhitungan SHU**: Simulasi dan distribusi SHU tahunan
- **Pengaturan Koperasi**: Konfigurasi persentase, suku bunga, dan parameter operasional

### 7. Notifikasi dan Pengingat
- **Notifikasi Status**: Update status transaksi, pengajuan, dan persetujuan
- **Pengingat Tagihan**: Reminder jatuh tempo pembayaran
- **Pemberitahuan SHU**: Informasi distribusi dan pencairan SHU

## Keunggulan Sistem

- **Responsif**: Interface yang mudah digunakan di berbagai perangkat
- **Transparansi**: Perhitungan yang jelas dan dapat diaudit
- **Keamanan**: Validasi NIK dan nomor telepon untuk mencegah duplikasi data
- **Fleksibilitas**: Pengaturan dinamis untuk tenggat waktu dan jatuh tempo tagihan
- **Laporan Lengkap**: Statistik dan laporan untuk pengambilan keputusan

## Cara Penggunaan

### Untuk Anggota Biasa
1. Daftar akun baru
2. Akses marketplace untuk jual beli produk
3. Upgrade menjadi anggota tetap untuk akses fitur simpan pinjam

### Untuk Anggota Tetap
1. Kelola simpanan (pokok, wajib, sukarela)
2. Ajukan pinjaman sesuai kebutuhan
3. Pantau tagihan dan jatuh tempo
4. Terima SHU sesuai kontribusi

### Untuk Administrator
1. Kelola pendaftaran anggota baru
2. Verifikasi transaksi simpanan dan pinjaman
3. Konfigurasi parameter koperasi
4. Kalkulasi dan distribusi SHU

## Persyaratan Sistem

- PHP 8.1+
- Laravel 10.x
- Filament v3
- Database MySQL
- Web Server (Apache/Nginx)

## Instalasi

```bash
# Clone repository
git clone https://github.com/username/koperasi_kita.git

# Masuk ke direktori project
cd koperasi_kita

# Install dependencies
composer install
npm install

# Setup environment
cp .env.example .env
php artisan key:generate

# Migrasi database
php artisan migrate --seed

# Jalankan aplikasi
php artisan serve
npm run dev
```
## Lisensi

Koperasi Kita dilisensikan di bawah [MIT license](https://opensource.org/licenses/MIT).

---

© 2025 KoperasiKita.
