<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Produk;
use App\Models\Kategori;
use App\Models\Supplier;
use App\Models\Satuan;
use App\Models\ProdukKonversi; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB; 
use Illuminate\Support\Facades\Storage; // Untuk menghapus gambar

class ProdukController extends Controller
{
    /**
     * Menampilkan daftar semua produk (SUDAH DENGAN FUNGSI SEARCH).
     * Rute: GET /admin/produk
     */
    public function index(Request $request)
    {
        // 1. Mulai query
        $query = Produk::with(['kategori', 'supplier', 'satuanDasar']);

        // 2. Pencarian
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nama_produk', 'like', '%' . $search . '%')
                  ->orWhere('id_produk', 'like', '%' . $search . '%');
            });
        }

        // 3. Ambil Data
        $produks = $query->latest()->paginate(10);
        
        // 4. Load Relasi Penting
        $produks->load([
            'produkKonversis.satuan', // Data konversi (DUS -> PCS)
            'stokMasukDetails',       // Riwayat Stok Masuk
            'detailTransaksis'        // Riwayat Penjualan (Stok Keluar)
        ]);

        // 5. KALKULASI STOK REAL-TIME (Masuk - Keluar)
        $produks->getCollection()->transform(function ($produk) {
            $satuan_dasar_nama = strtoupper($produk->satuanDasar->nama_satuan ?? 'PCS');
            
            // --- A. HITUNG TOTAL STOK MASUK (Dalam PCS) ---
            $total_masuk_pcs = 0;
            foreach ($produk->stokMasukDetails as $masuk) {
                $jumlah = $masuk->jumlah;
                $satuan = strtoupper($masuk->satuan);
                
                // Jika satuan beda dengan dasar, cari konversinya
                if ($satuan !== $satuan_dasar_nama) {
                    $konversi = $produk->produkKonversis->first(function ($k) use ($satuan) {
                        return strtoupper($k->satuan->nama_satuan) === $satuan;
                    });
                    if ($konversi) {
                        $jumlah = $jumlah * $konversi->nilai_konversi;
                    }
                }
                $total_masuk_pcs += $jumlah;
            }
            
            // --- B. HITUNG TOTAL STOK KELUAR / PENJUALAN (Dalam PCS) ---
            $total_keluar_pcs = 0;
            foreach ($produk->detailTransaksis as $keluar) {
                $jumlah = $keluar->jumlah;
                $satuan = strtoupper($keluar->satuan);
                
                // Logika Konversi yang SAMA untuk Penjualan
                if ($satuan !== $satuan_dasar_nama) {
                    $konversi = $produk->produkKonversis->first(function ($k) use ($satuan) {
                        return strtoupper($k->satuan->nama_satuan) === $satuan;
                    });
                    if ($konversi) {
                        $jumlah = $jumlah * $konversi->nilai_konversi;
                    }
                }
                $total_keluar_pcs += $jumlah;
            }
            
            // C. Stok Akhir
            $produk->stok_saat_ini = $total_masuk_pcs - $total_keluar_pcs;
            
            return $produk;
        });

        $produks->appends($request->only('search'));
        
        return view('admin.produk.index', compact('produks'));
    }
    public function create()
    {
        // Ambil semua data master untuk dropdown
        $kategoris = \App\Models\Kategori::all();
        $suppliers = \App\Models\Supplier::all();
        $satuans = \App\Models\Satuan::all();
        
        // Kirim semua data ke view
        return view('admin.produk.create', compact('kategoris', 'suppliers', 'satuans'));
    }

    /**
     * Menyimpan produk baru ke database.
     * Rute: POST /admin/produk
     */
    public function store(Request $request)
    {
        // 1. Validasi Data Umum & Satuan Dasar
        $validatedData = $request->validate([
            'id_produk' => 'required|string|max:50|unique:produk,id_produk',
            'nama_produk' => 'required|string|max:150',
            'id_kategori' => 'required|exists:kategori,id_kategori',
            'id_supplier' => 'required|exists:supplier,id_supplier',
            'deskripsi' => 'nullable|string',
            'gambar' => 'nullable|image|max:2048',
            'id_satuan_dasar' => 'required|exists:satuan,id_satuan',
            'harga_pokok_dasar' => 'required|numeric|min:0',
            'harga_jual_dasar' => 'required|numeric|min:0',
            'konversi' => 'nullable|array',
            'konversi.*.id_satuan_konversi' => 'required_with:konversi|exists:satuan,id_satuan',
            'konversi.*.nilai_konversi' => 'required_with:konversi|integer|min:1',
            'konversi.*.harga_pokok_konversi' => 'required_with:konversi|numeric|min:0',
            'konversi.*.harga_jual_konversi' => 'required_with:konversi|numeric|min:0',
        ]);

        $gambarPath = null;
        if ($request->hasFile('gambar')) {
            $gambarPath = $request->file('gambar')->store('produk', 'public');
        }

        try {
            DB::beginTransaction();

            // 4. Simpan ke tabel 'produk' (Data Master)
            $produk = Produk::create([
                'id_produk' => $validatedData['id_produk'],
                'nama_produk' => $validatedData['nama_produk'],
                'id_kategori' => $validatedData['id_kategori'],
                'id_supplier' => $validatedData['id_supplier'],
                'deskripsi' => $validatedData['deskripsi'],
                'gambar' => $gambarPath,
                'status_produk' => 'aktif',
                'id_satuan_dasar' => $validatedData['id_satuan_dasar'],
                'harga_pokok_dasar' => $validatedData['harga_pokok_dasar'],
                'harga_jual_dasar' => $validatedData['harga_jual_dasar'],
            ]);

            // 5. Simpan ke tabel 'produk_konversi' (Jika ada)
            if ($request->has('konversi')) {
                foreach ($request->konversi as $konv) {
                    $produk->produkKonversis()->create([
                        'id_satuan_konversi' => $konv['id_satuan_konversi'],
                        'nilai_konversi' => $konv['nilai_konversi'],
                        'harga_pokok_konversi' => $konv['harga_pokok_konversi'],
                        'harga_jual_konversi' => $konv['harga_jual_konversi'],
                    ]);
                }
            }
            
            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal menyimpan produk: ' . $e->getMessage());
        }

        return redirect()->route('admin.produk.index')
                         ->with('success', 'Produk baru berhasil ditambahkan.');
    }

    /**
     * Menampilkan form untuk mengedit produk.
     * Rute: GET /admin/produk/{produk}/edit
     */
    public function edit(Produk $produk)
    {
        $kategoris = Kategori::all();
        $suppliers = Supplier::all();
        $satuans = Satuan::all();

        $produk->load('produkKonversis');

        return view('admin.produk.edit', compact('produk', 'kategoris', 'suppliers', 'satuans'));
    }

    /**
     * Mengupdate produk di database.
     * Rute: PUT/PATCH /admin/produk/{produk}
     */
    public function update(Request $request, Produk $produk)
    {
        // 1. Validasi Data
        $validatedData = $request->validate([
            'nama_produk' => 'required|string|max:150',
            'id_kategori' => 'required|exists:kategori,id_kategori',
            'id_supplier' => 'required|exists:supplier,id_supplier',
            'deskripsi' => 'nullable|string',
            'gambar' => 'nullable|image|max:2048',
            
            'id_satuan_dasar' => 'required|exists:satuan,id_satuan',
            'harga_pokok_dasar' => 'required|numeric|min:0',
            'harga_jual_dasar' => 'required|numeric|min:0',
            
            'konversi' => 'nullable|array',
            'konversi.*.id_satuan_konversi' => 'required_with:konversi|exists:satuan,id_satuan',
            'konversi.*.nilai_konversi' => 'required_with:konversi|integer|min:1',
            'konversi.*.harga_pokok_konversi' => 'required_with:konversi|numeric|min:0',
            'konversi.*.harga_jual_konversi' => 'required_with:konversi|numeric|min:0',
        ]);

        // 2. Handle Upload Gambar (Jika ada gambar baru)
        $gambarPath = $produk->gambar; 
        if ($request->hasFile('gambar')) {
            // Storage::disk('public')->delete($produk->gambar); 
            $gambarPath = $request->file('gambar')->store('produk', 'public');
        }

        // 3. Gunakan DB Transaction
        try {
            DB::beginTransaction();

            // 4. Update tabel 'produk' (Data Master)
            $produk->update([
                'nama_produk' => $validatedData['nama_produk'],
                'id_kategori' => $validatedData['id_kategori'],
                'id_supplier' => $validatedData['id_supplier'],
                'deskripsi' => $validatedData['deskripsi'],
                'gambar' => $gambarPath,
                'id_satuan_dasar' => $validatedData['id_satuan_dasar'],
                'harga_pokok_dasar' => $validatedData['harga_pokok_dasar'],
                'harga_jual_dasar' => $validatedData['harga_jual_dasar'],
            ]);

            // 5. Update/Hapus/Buat data di 'produk_konversi'
            $produk->produkKonversis()->delete(); 
            
            if ($request->has('konversi')) {
                foreach ($request->konversi as $konv) {
                    $produk->produkKonversis()->create([ 
                        'id_satuan_konversi' => $konv['id_satuan_konversi'],
                        'nilai_konversi' => $konv['nilai_konversi'],
                        'harga_pokok_konversi' => $konv['harga_pokok_konversi'],
                        'harga_jual_konversi' => $konv['harga_jual_konversi'],
                    ]);
                }
            }
            
            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal mengupdate produk: ' . $e->getMessage());
        }

        return redirect()->route('admin.produk.index')
                         ->with('success', 'Produk berhasil diupdate.');
    }

    /**
     * Menghapus produk dari database.
     * Rute: DELETE /admin/produk/{produk}
     */
    public function destroy(Produk $produk)
    {
        // 1. (Opsional tapi direkomendasikan) Hapus gambar produk dari storage
        if ($produk->gambar) {
            // Storage::disk('public')->delete($produk->gambar);
        }

        // 2. Hapus data produk dari database.
        $produk->delete();

        return redirect()->route('admin.produk.index')
                         ->with('success', 'Produk berhasil dihapus.');
    }

    /**
     * Mengubah status produk (aktif/nonaktif).
     * Rute: PATCH /admin/produk/{produk}/toggle-status
     */
    public function toggleStatus(Produk $produk)
    {
        $produk->status_produk = ($produk->status_produk == 'aktif') ? 'nonaktif' : 'aktif';

        $produk->save();

        return redirect()->route('admin.produk.index')
                         ->with('success', 'Status produk berhasil diperbarui.');
    }
}