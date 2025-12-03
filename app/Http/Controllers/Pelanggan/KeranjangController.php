<?php

namespace App\Http\Controllers\Pelanggan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Keranjang;
use App\Models\Produk;
use Illuminate\Support\Facades\Auth;

class KeranjangController extends Controller
{
    // 1. Tambah ke Keranjang
    public function tambah(Request $request)
    {
        $request->validate([
            'id_produk' => 'required|exists:produk,id_produk',
            'satuan'    => 'required|string',
            'harga'     => 'required|numeric',
        ]);

        $userId = Auth::id();

        // Cek apakah barang ini sudah ada di keranjang user dengan satuan yang SAMA?
        $itemAda = Keranjang::where('id_user', $userId)
                            ->where('id_produk', $request->id_produk)
                            ->where('satuan', $request->satuan)
                            ->first();

        if ($itemAda) {
            // Jika ada, tambahkan jumlahnya (+1)
            $itemAda->increment('jumlah');
        } else {
            // Jika belum ada, buat baru
            Keranjang::create([
                'id_user'   => $userId,
                'id_produk' => $request->id_produk,
                'satuan'    => $request->satuan,
                'harga_saat_ini' => $request->harga,
                'jumlah'    => 1
            ]);
        }
        // --- PERBAIKAN: RESPON UNTUK AJAX ---
        if ($request->wantsJson()) {
            // Hitung total jenis barang di keranjang user saat ini
            $totalItems = Keranjang::where('id_user', $userId)->count();
            
            return response()->json([
                'success' => true,
                'message' => 'Produk berhasil masuk keranjang!',
                'cart_count' => $totalItems // Kirim jumlah terbaru
            ]);
        }

        // Redirect kembali dengan pesan sukses
        return back()->with('success', 'Produk berhasil masuk keranjang!');
    }
    // Update Jumlah Barang
    public function update(Request $request, $id)
    {
        $request->validate(['jumlah' => 'required|integer|min:1']);
        
        $item = Keranjang::where('id_keranjang', $id)->where('id_user', Auth::id())->firstOrFail();
        
        // Update Database
        $item->update(['jumlah' => $request->jumlah]);
        
        // --- RESPON JSON (PENTING UNTUK AJAX) ---
        if ($request->ajax() || $request->wantsJson()) {
            
            // Hitung Ulang Subtotal Item Ini
            $subtotal = $item->harga_saat_ini * $item->jumlah;

            // Hitung Ulang Grand Total Keranjang
            $seluruhKeranjang = Keranjang::where('id_user', Auth::id())->get();
            $grandTotal = $seluruhKeranjang->sum(fn($i) => $i->harga_saat_ini * $i->jumlah);
            
            // Hitung Jumlah Item untuk Badge Navbar
            $cartCount = $seluruhKeranjang->count();

            return response()->json([
                'success' => true,
                'subtotal' => number_format($subtotal, 0, ',', '.'),
                'grand_total' => number_format($grandTotal, 0, ',', '.'),
                'cart_count' => $cartCount
            ]);
        }
        // ----------------------------------------
        
        return back()->with('success', 'Keranjang diperbarui');
    }
    // Hapus Barang
    public function destroy($id)
    {
        $item = Keranjang::where('id_keranjang', $id)->where('id_user', Auth::id())->firstOrFail();
        $item->delete();
        
        return back()->with('success', 'Barang dihapus dari keranjang.');
    }
    
    // 2. Halaman Keranjang (Nanti kita isi)
    public function index() {
        $keranjang = Keranjang::with('produk')->where('id_user', Auth::id())->get();
        return view('pelanggan.keranjang.index', compact('keranjang'));
    }

}