<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Produk;
use App\Models\Keranjang; // <-- Jangan lupa import ini
use Illuminate\Support\Facades\Auth; // <-- Dan ini

class HomeController extends Controller
{
    public function index(Request $request)
    {
        // 1. Ambil Kategori untuk Menu
        $kategoris = \App\Models\Kategori::all(); 

        // 2. Query Produk Dasar
        $query = Produk::with(['satuanDasar', 'kategori', 'stokMasukDetails', 'detailTransaksis', 'produkKonversis.satuan'])
                       ->where('status_produk', 'aktif');

        // 3. Logika Pencarian (Search)
        if ($request->has('search') && $request->search != '') {
            $query->where('nama_produk', 'like', '%' . $request->search . '%');
        }

        // 4. LOGIKA FILTER KATEGORI (BARU)
        if ($request->has('kategori') && $request->kategori != '') {
            $query->where('id_kategori', $request->kategori);
        }

        // 5. Ambil Data & Pagination
        $produks = $query->latest()->paginate(12);
        
        // Agar filter tidak hilang saat pindah halaman 1, 2, 3...
        $produks->appends($request->all()); 
        $cartProductIds = [];
        if (Auth::check()) {
            // Ambil hanya kolom id_produk, jadikan array sederhana
            $cartProductIds = Keranjang::where('id_user', Auth::id())
                                       ->pluck('id_produk')
                                       ->toArray();
        }

        // 6. Kalkulasi Stok (Sama seperti sebelumnya)
        $produks->getCollection()->transform(function ($produk) {
            $satuan_dasar = $produk->satuanDasar->nama_satuan ?? 'PCS';
            
            // Hitung Masuk
            $masuk = 0;
            foreach ($produk->stokMasukDetails as $d) {
                $jml = $d->jumlah;
                if ($d->satuan !== $satuan_dasar) {
                    $konv = $produk->produkKonversis->firstWhere('satuan.nama_satuan', $d->satuan);
                    if ($konv) $jml *= $konv->nilai_konversi;
                }
                $masuk += $jml;
            }

            // Hitung Keluar
            $keluar = 0;
            foreach ($produk->detailTransaksis as $d) {
                $jml = $d->jumlah;
                if ($d->satuan !== $satuan_dasar) {
                    $konv = $produk->produkKonversis->firstWhere('satuan.nama_satuan', $d->satuan);
                    if ($konv) $jml *= $konv->nilai_konversi;
                }
                $keluar += $jml;
            }

            $stok_dasar = $masuk - $keluar;

            // Unit List (Untuk Dropdown Frontend)
            $units = [];
            $units[] = [
                'name' => $satuan_dasar,
                'price' => $produk->harga_jual_dasar,
                'stock_display' => $stok_dasar 
            ];

            foreach ($produk->produkKonversis as $konv) {
                $stok_konv = ($konv->nilai_konversi > 0) ? floor($stok_dasar / $konv->nilai_konversi) : 0;
                $units[] = [
                    'name' => $konv->satuan->nama_satuan,
                    'price' => $konv->harga_jual_konversi,
                    'stock_display' => $stok_konv
                ];
            }

            $produk->units_list = $units;
            $produk->stok_ready = $stok_dasar; // Indikator utama

            return $produk;
        });

        return view('welcome', compact('produks', 'kategoris', 'cartProductIds'));
    }
}