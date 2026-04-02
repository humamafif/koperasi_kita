<?php

namespace App\Http\Controllers;

use App\Models\Produk;
use Illuminate\Http\Request;

class WelcomeController extends Controller
{
    public function index()
    {
        // Ambil produk yang aktif dan urutkan berdasarkan terbaru
        $products = Produk::where('aktif', true)
            ->orderBy('created_at', 'desc')
            ->take(6) // Tampilkan 6 produk terbaru
            ->get();

        return view('welcome', compact('products'));
    }

    public function showAllProducts(Request $request)
    {
        $query = Produk::where('aktif', true);

        // Filter berdasarkan kategori jika ada
        if ($request->has('kategori') && $request->kategori != 'semua') {
            $query->where('kategori', $request->kategori);
        }

        // Filter berdasarkan pencarian jika ada
        if ($request->has('search') && !empty($request->search)) {
            $query->where('nama', 'like', '%' . $request->search . '%')
                ->orWhere('deskripsi', 'like', '%' . $request->search . '%');
        }

        // Ambil semua data setelah filter untuk sorting manual
        $allProducts = $query->get()->toArray();

        // Implementasi Quick Sort berdasarkan harga
        $sortOrder = $request->get('sort', 'latest');

        if ($sortOrder === 'price_asc' || $sortOrder === 'price_desc') {
            $allProducts = $this->quickSort($allProducts, 'harga', $sortOrder === 'price_asc');
        } else {
            // Default sort by created_at desc (manual array sort for consistency)
            usort($allProducts, function ($a, $b) {
                return strtotime($b['created_at']) - strtotime($a['created_at']);
            });
        }

        $products = collect($allProducts);

        $categories = Produk::where('aktif', true)
            ->select('kategori')
            ->distinct()
            ->pluck('kategori');

        return view('products', compact('products', 'categories'));
    }

    /**
     * Algoritma Quick Sort
     */
    private function quickSort(array $items, $field, $ascending = true)
    {
        if (count($items) < 2) {
            return $items;
        }

        $left = $right = [];
        reset($items);
        $pivot_key = key($items);
        $pivot = array_shift($items);

        foreach ($items as $item) {
            if ($ascending) {
                if ($item[$field] < $pivot[$field]) {
                    $left[] = $item;
                } else {
                    $right[] = $item;
                }
            } else {
                if ($item[$field] > $pivot[$field]) {
                    $left[] = $item;
                } else {
                    $right[] = $item;
                }
            }
        }

        return array_merge(
            $this->quickSort($left, $field, $ascending),
            [$pivot],
            $this->quickSort($right, $field, $ascending)
        );
    }

    public function showProduct($id)
    {
        $product = Produk::findOrFail($id);

        // Ambil produk terkait dengan kategori yang sama
        $relatedProducts = Produk::where('kategori', $product->kategori)
            ->where('id', '!=', $product->id)
            ->where('aktif', true)
            ->take(4)
            ->get();

        return view('product-detail', compact('product', 'relatedProducts'));
    }
}
