<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Produk;

class ProdukSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            [
                'nama' => 'Beras Premium 5kg',
                'deskripsi' => 'Beras kualitas super, pulen dan bersih.',
                'harga' => 75000,
                'stok' => 50,
                'kategori' => 'Sembako',
                'aktif' => true,
                'user_id' => 3,
            ],
            [
                'nama' => 'Minyak Goreng 2L',
                'deskripsi' => 'Minyak goreng kelapa sawit jernih.',
                'harga' => 34000,
                'stok' => 100,
                'kategori' => 'Sembako',
                'aktif' => true,
                'user_id' => 3,
            ],
            [
                'nama' => 'Gula Pasir 1kg',
                'deskripsi' => 'Gula pasir putih tebu asli.',
                'harga' => 18000,
                'stok' => 200,
                'kategori' => 'Sembako',
                'aktif' => true,
                'user_id' => 3,
            ],
            [
                'nama' => 'Sabun Cuci Piring 800ml',
                'deskripsi' => 'Sabun cuci piring konsentrat tinggi.',
                'harga' => 15500,
                'stok' => 80,
                'kategori' => 'Kebersihan',
                'aktif' => true,
                'user_id' => 3,
            ],
            [
                'nama' => 'Susu Kaleng 370g',
                'deskripsi' => 'Susu kental manis untuk berbagai sajian.',
                'harga' => 12000,
                'stok' => 150,
                'kategori' => 'Minuman',
                'aktif' => true,
                'user_id' => 3,
            ],
            [
                'nama' => 'Teh Celup (Isi 25)',
                'deskripsi' => 'Teh celup aroma melati pilihan.',
                'harga' => 6500,
                'stok' => 300,
                'kategori' => 'Minuman',
                'aktif' => true,
                'user_id' => 3,
            ],
            [
                'nama' => 'Kopi Bubuk 165g',
                'deskripsi' => 'Kopi murni dengan aromanya yang khas.',
                'harga' => 14000,
                'stok' => 120,
                'kategori' => 'Minuman',
                'aktif' => true,
                'user_id' => 3,
            ],
            [
                'nama' => 'Deterjen Bubuk 800g',
                'deskripsi' => 'Deterjen pembersih pakaian anti noda.',
                'harga' => 22000,
                'stok' => 90,
                'kategori' => 'Kebersihan',
                'aktif' => true,
                'user_id' => 3,
            ],
            [
                'nama' => 'Telur Ayam (1kg)',
                'deskripsi' => 'Telur ayam negeri segar dan berkualitas.',
                'harga' => 29000,
                'stok' => 30,
                'kategori' => 'Sembako',
                'aktif' => true,
                'user_id' => 3,
            ],
            [
                'nama' => 'Mie Instan (Dus)',
                'deskripsi' => 'Satu dus mie instan rasa kaldu ayam.',
                'harga' => 115000,
                'stok' => 25,
                'kategori' => 'Sembako',
                'aktif' => true,
                'user_id' => 3,
            ],
            [
                'nama' => 'Pasta Gigi 190g',
                'deskripsi' => 'Pasta gigi pencegah gigi berlubang.',
                'harga' => 13500,
                'stok' => 110,
                'kategori' => 'Kebersihan',
                'aktif' => true,
                'user_id' => 3,
            ],
            [
                'nama' => 'Shampoo 170ml',
                'deskripsi' => 'Shampoo anti ketombe dan rambut rontok.',
                'harga' => 26000,
                'stok' => 70,
                'kategori' => 'Kebersihan',
                'aktif' => true,
                'user_id' => 3,
            ],
        ];

        foreach ($products as $product) {
            Produk::create($product);
        }
    }
}
