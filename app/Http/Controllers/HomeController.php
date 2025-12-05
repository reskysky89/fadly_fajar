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
        $kategoris = \App\Models\Kategori::all(); 

        $query = Produk::with(['satuanDasar', 'kategori', 'stokMasukDetails', 'detailTransaksis', 'produkKonversis.satuan'])
                       ->where('status_produk', 'aktif');

        if ($request->has('search') && $request->search != '') {
            $query->where('nama_produk', 'like', '%' . $request->search . '%');
        }

        if ($request->has('kategori') && $request->kategori != '') {
            $query->where('id_kategori', $request->kategori);
        }

        $produks = $query->latest()->paginate(12);
        $produks->appends($request->all()); 

        // Ambil ID Keranjang (Untuk Badge)
        $cartProductIds = [];
        if (\Illuminate\Support\Facades\Auth::check()) {
            $cartProductIds = \App\Models\Keranjang::where('id_user', \Illuminate\Support\Facades\Auth::id())
                                       ->pluck('id_produk')
                                       ->toArray();
        }

        $ulasanTerbaru = \App\Models\Ulasan::with(['user', 'produk'])
                                           ->latest()
                                           ->take(6) // Ambil 6 ulasan
                                           ->get();
        

        // Kalkulasi Stok & Satuan
        $produks->getCollection()->transform(function ($produk) {
            $satuan_dasar = $produk->satuanDasar->nama_satuan ?? 'PCS';
            
            // Hitung Stok Real (PCS)
            $masuk = 0;
            foreach ($produk->stokMasukDetails as $d) {
                $jml = $d->jumlah;
                if ($d->satuan !== $satuan_dasar) {
                    $konv = $produk->produkKonversis->firstWhere('satuan.nama_satuan', $d->satuan);
                    if ($konv) $jml *= $konv->nilai_konversi;
                }
                $masuk += $jml;
            }

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

            // --- PERBAIKAN DI SINI: Tambahkan 'conversion' ---
            $units = [];
            
            // 1. Satuan Dasar
            $units[] = [
                'name' => $satuan_dasar,
                'price' => $produk->harga_jual_dasar,
                'conversion' => 1, // <-- Default 1
                'stock_display' => $stok_dasar 
            ];

            // 2. Satuan Konversi
            foreach ($produk->produkKonversis as $konv) {
                $nilai_konversi = $konv->nilai_konversi > 0 ? $konv->nilai_konversi : 1;
                $stok_konv = floor($stok_dasar / $nilai_konversi);
                
                $units[] = [
                    'name' => $konv->satuan->nama_satuan,
                    'price' => $konv->harga_jual_konversi,
                    'conversion' => $nilai_konversi, // <-- INI KUNCINYA
                    'stock_display' => $stok_konv
                ];
            
            }
            // -----------------------------------------------

            $produk->units_list = $units;
            $produk->stok_ready = $stok_dasar; 

            return $produk;
        });

        return view('welcome', compact('produks', 'kategoris', 'cartProductIds', 'ulasanTerbaru'));
    }
    public function cekStok($id)
    {
        $produk = Produk::with(['stokMasukDetails', 'detailTransaksis', 'satuanDasar', 'produkKonversis.satuan'])
                        ->where('id_produk', $id)
                        ->first();

        if (!$produk) return response()->json(['stok' => 0]);

        $satuan_dasar = $produk->satuanDasar->nama_satuan ?? 'PCS';
            
        // 1. Hitung Masuk
        $masuk = 0;
        foreach ($produk->stokMasukDetails as $d) {
            $jml = $d->jumlah;
            if ($d->satuan !== $satuan_dasar) {
                $konv = $produk->produkKonversis->firstWhere('satuan.nama_satuan', $d->satuan);
                if ($konv) $jml *= $konv->nilai_konversi;
            }
            $masuk += $jml;
        }

        // 2. Hitung Keluar
        $keluar = 0;
        foreach ($produk->detailTransaksis as $d) {
            $jml = $d->jumlah;
            if ($d->satuan !== $satuan_dasar) {
                $konv = $produk->produkKonversis->firstWhere('satuan.nama_satuan', $d->satuan);
                if ($konv) $jml *= $konv->nilai_konversi;
            }
            $keluar += $jml;
        }

        $stok_real = $masuk - $keluar;

        return response()->json(['stok' => $stok_real]);
    }
}