<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Produk;
use App\Models\Kategori;
use App\Models\Supplier;
use App\Models\Satuan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB; 
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str; // Tetap pakai ini biar nama file aman

class ProdukController extends Controller
{
    /**
     * Menampilkan daftar semua produk.
     */
    public function index(Request $request)
    {
        $query = Produk::with(['kategori', 'supplier', 'satuanDasar']);

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nama_produk', 'like', '%' . $search . '%')
                  ->orWhere('id_produk', 'like', '%' . $search . '%')
                  ->orWhereHas('supplier', function($subQuery) use ($search) {
                      $subQuery->where('nama_supplier', 'like', '%' . $search . '%');
                  });
            });
        }

        $produks = $query->latest()->paginate(10);
        $produks->load(['produkKonversis.satuan', 'stokMasukDetails', 'detailTransaksis']);

        // Kalkulasi Stok Real-time
        $produks->getCollection()->transform(function ($produk) {
            $satuan_dasar_nama = strtoupper($produk->satuanDasar->nama_satuan ?? 'PCS');
            
            $total_masuk_pcs = 0;
            foreach ($produk->stokMasukDetails as $masuk) {
                $jumlah = $masuk->jumlah;
                $satuan = strtoupper($masuk->satuan);
                if ($satuan !== $satuan_dasar_nama) {
                    $konversi = $produk->produkKonversis->first(function ($k) use ($satuan) { return strtoupper($k->satuan->nama_satuan) === $satuan; });
                    if ($konversi) { $jumlah = $jumlah * $konversi->nilai_konversi; }
                }
                $total_masuk_pcs += $jumlah;
            }
            
            $total_keluar_pcs = 0;
            foreach ($produk->detailTransaksis as $keluar) {
                $jumlah = $keluar->jumlah;
                $satuan = strtoupper($keluar->satuan);
                if ($satuan !== $satuan_dasar_nama) {
                    $konversi = $produk->produkKonversis->first(function ($k) use ($satuan) { return strtoupper($k->satuan->nama_satuan) === $satuan; });
                    if ($konversi) { $jumlah = $jumlah * $konversi->nilai_konversi; }
                }
                $total_keluar_pcs += $jumlah;
            }
            
            $produk->stok_saat_ini = $total_masuk_pcs - $total_keluar_pcs;
            return $produk;
        });

        $produks->appends($request->only('search'));
        
        if ($request->ajax()) {
            return view('admin.produk.table_body', compact('produks'))->render();
        }

        return view('admin.produk.index', compact('produks'));
    }

    public function create()
    {
        $kategoris = \App\Models\Kategori::all();
        $suppliers = \App\Models\Supplier::all();
        $satuans = \App\Models\Satuan::all();
        return view('admin.produk.create', compact('kategoris', 'suppliers', 'satuans'));
    }

    /**
     * SIMPAN PRODUK BARU
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'id_produk' => 'required|string|max:50|unique:produk,id_produk',
            'nama_produk' => 'required|string|max:150',
            'id_kategori' => 'required|exists:kategori,id_kategori',
            'id_supplier' => 'required|exists:supplier,id_supplier',
            'deskripsi' => 'nullable|string',
            'gambar' => 'nullable|image|max:2048',
            'id_satuan_dasar' => 'required|exists:satuan,id_satuan',
            'harga_pokok_dasar' => 'required|numeric|min:0',
            'harga_jual_dasar' => 'required|numeric|min:0|gte:harga_pokok_dasar',
            'konversi' => ['nullable', 'array'],
            'konversi.*.id_satuan_konversi' => 'required_with:konversi|exists:satuan,id_satuan',
            'konversi.*.nilai_konversi' => 'required_with:konversi|integer|min:1',
            'konversi.*.harga_pokok_konversi' => 'required_with:konversi|numeric|min:0',
            'konversi.*.harga_jual_konversi' => 'required_with:konversi|numeric|min:0',
        ]);

        $gambarPath = null;
        
        // --- LOGIKA UPLOAD ASLI (BAWAAN LARAVEL) ---
        $gambarPath = null;
        if ($request->hasFile('gambar')) {
            $file = $request->file('gambar');
            
            // 1. Buat Nama File Unik & Bersih
            $name = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $cleanName = Str::slug($name);
            $filename = time() . '_' . $cleanName . '.' . $file->getClientOriginalExtension();

            // 2. PINDAHKAN LANGSUNG KE FOLDER PUBLIC (Jalan Tol)
            // Gambar akan masuk ke: C:\laragon\www\proyek\public\uploads\produk
            $file->move(public_path('uploads/produk'), $filename);

            // 3. Simpan Alamatnya (Tanpa 'public/' atau 'storage/')
            $gambarPath = 'uploads/produk/' . $filename;
        }
        // -------------------------------------------

        try {
            DB::beginTransaction();

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
            return back()->withInput()->with('error', 'Gagal menyimpan: ' . $e->getMessage());
        }

        return redirect()->route('admin.produk.index')->with('success', 'Produk berhasil ditambahkan.');
    }

    public function edit(Produk $produk)
    {
        $kategoris = \App\Models\Kategori::all();
        $suppliers = \App\Models\Supplier::all();
        $satuans = \App\Models\Satuan::all();
        $produk->load('produkKonversis');
        return view('admin.produk.edit', compact('produk', 'kategoris', 'suppliers', 'satuans'));
    }

    /**
     * UPDATE PRODUK
     */
    public function update(Request $request, Produk $produk)
    {
        $validatedData = $request->validate([
            'nama_produk' => 'required|string|max:150',
            'id_kategori' => 'required|exists:kategori,id_kategori',
            'id_supplier' => 'required|exists:supplier,id_supplier',
            'deskripsi' => 'nullable|string',
            'gambar' => 'nullable|image|max:2048',
            'id_satuan_dasar' => 'required|exists:satuan,id_satuan',
            'harga_pokok_dasar' => 'required|numeric|min:0',
            'harga_jual_dasar' => 'required|numeric|min:0|gte:harga_pokok_dasar',
            'konversi' => ['nullable', 'array'],
        ]);

        // --- LOGIKA UPLOAD UPDATE (BAWAAN LARAVEL) ---
        $gambarPath = $produk->gambar; 

        if ($request->hasFile('gambar')) {
            $file = $request->file('gambar');
            
            // 1. Hapus Gambar Lama (Cek langsung di folder public)
            if ($produk->gambar && file_exists(public_path($produk->gambar))) {
                unlink(public_path($produk->gambar));
            }

            // 2. Buat Nama Baru
            $name = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $cleanName = Str::slug($name);
            $filename = time() . '_' . $cleanName . '.' . $file->getClientOriginalExtension();

            // 3. Simpan ke Public
            $file->move(public_path('uploads/produk'), $filename);

            $gambarPath = 'uploads/produk/' . $filename;
        }
        // ---------------------------------------------

        try {
            DB::beginTransaction();

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
            return back()->withInput()->with('error', 'Gagal update: ' . $e->getMessage());
        }

        return redirect()->route('admin.produk.index')->with('success', 'Produk berhasil diupdate.');
    }

    public function destroy(Produk $produk)
    {
        if ($produk->gambar) {
            if(Storage::exists('public/' . $produk->gambar)){
                 Storage::delete('public/' . $produk->gambar);
            }
        }
        $produk->delete();
        return redirect()->route('admin.produk.index')->with('success', 'Produk berhasil dihapus.');
    }

    public function toggleStatus(Produk $produk)
    {
        $produk->status_produk = ($produk->status_produk == 'aktif') ? 'nonaktif' : 'aktif';
        $produk->save();
        return redirect()->route('admin.produk.index')->with('success', 'Status produk berhasil diperbarui.');
    }
}