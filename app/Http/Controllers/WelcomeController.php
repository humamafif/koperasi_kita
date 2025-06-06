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

        $products = $query->orderBy('created_at', 'desc')
            ->paginate(12);

        $categories = Produk::where('aktif', true)
            ->select('kategori')
            ->distinct()
            ->pluck('kategori');

        return view('products', compact('products', 'categories'));
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
