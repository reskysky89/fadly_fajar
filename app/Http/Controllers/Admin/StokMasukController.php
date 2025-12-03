<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Produk;
use App\Models\Supplier;
use App\Models\StokMasukBatch;
use App\Models\StokMasukDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class StokMasukController extends Controller
{
    /**
     * Menampilkan halaman Riwayat Stok Masuk
     */
    public function index(Request $request)
    {
        // 1. Mulai Query (Eager Load relasi untuk performa)
        $query = StokMasukBatch::with(['user', 'supplier', 'userDiubah'])->latest();

        // 2. Logika Filter Tanggal (Yang sudah ada)
        if ($request->filled('tanggal_mulai') && $request->filled('tanggal_akhir')) {
            $query->whereBetween('tanggal_masuk', [$request->tanggal_mulai, $request->tanggal_akhir]);
        }

        // 3. LOGIKA PENCARIAN BARU (Supplier OR Keterangan)
        if ($request->filled('search')) {
            $search = $request->search;
            
            $query->where(function($q) use ($search) {
                // Cari di kolom keterangan
                $q->where('keterangan', 'like', '%' . $search . '%')
                  // ATAU Cari di nama supplier (lewat relasi)
                  ->orWhereHas('supplier', function($subQuery) use ($search) {
                      $subQuery->where('nama_supplier', 'like', '%' . $search . '%');
                  })
                  // ATAU Cari berdasarkan No Transaksi (Opsional, biar makin lengkap)
                  ->orWhere('id_batch_stok', 'like', '%' . $search . '%');
            });
        }

        // 4. Eksekusi Query
        $riwayatStok = $query->paginate(10);
        
        // 5. Append query string agar filter/search tidak hilang saat klik halaman 2
        $riwayatStok->appends($request->all());

        return view('admin.stok.index', compact('riwayatStok'));
    }

    /**
     * Menampilkan halaman form Input Stok Masuk
     */
    public function create()
    {
        $suppliers = Supplier::orderBy('nama_supplier')->get();
        return view('admin.stok.create', compact('suppliers'));
    }

    /**
     * LOGIKA PENCARIAN PRODUK (AJAX)
     */
    public function cariProduk(Request $request)
    {
        $search = $request->query('search');
        if (empty($search)) return response()->json([]);

        $produks = Produk::where('nama_produk', 'like', '%' . $search . '%')
                         ->orWhere('id_produk', 'like', '%' . $search . '%')
                         // Load relasi untuk hitung stok
                         ->with(['satuanDasar', 'produkKonversis.satuan', 'stokMasukDetails', 'detailTransaksis']) 
                         ->limit(10)
                         ->get();

        $hasil = [];

        foreach ($produks as $produk) {
            // --- LOGIKA HITUNG STOK REAL-TIME ---
            $total_masuk = 0;
            foreach ($produk->stokMasukDetails as $masuk) {
                $jumlah = $masuk->jumlah;
                if ($masuk->satuan !== ($produk->satuanDasar->nama_satuan ?? 'PCS')) {
                    $konv = $produk->produkKonversis->first(fn($k) => $k->satuan->nama_satuan === $masuk->satuan);
                    if ($konv) $jumlah *= $konv->nilai_konversi;
                }
                $total_masuk += $jumlah;
            }
            
            $total_keluar = 0;
            foreach ($produk->detailTransaksis as $keluar) {
                $jumlah = $keluar->jumlah;
                if ($keluar->satuan !== ($produk->satuanDasar->nama_satuan ?? 'PCS')) {
                    $konv = $produk->produkKonversis->first(fn($k) => $k->satuan->nama_satuan === $keluar->satuan);
                    if ($konv) $jumlah *= $konv->nilai_konversi;
                }
                $total_keluar += $jumlah;
            }
            
            $stok_sisa_pcs = $total_masuk - $total_keluar;
            // ------------------------------------

            // 1. Satuan Dasar
            $hasil[] = [
                'unique_id' => $produk->id_produk . '-' . $produk->id_satuan_dasar,
                'id_produk' => $produk->id_produk,
                'nama_produk' => $produk->nama_produk,
                'id_satuan' => $produk->id_satuan_dasar,
                'nama_satuan' => $produk->satuanDasar->nama_satuan, 
                'harga_pokok' => $produk->harga_pokok_dasar,
                // KIRIM DATA STOK
                'stok_real' => number_format($stok_sisa_pcs, 0, ',', '.') 
            ];

            // 2. Satuan Konversi
            foreach ($produk->produkKonversis as $konv) {
                // Hitung stok konversi
                $stok_konv = ($konv->nilai_konversi > 0) ? ($stok_sisa_pcs / $konv->nilai_konversi) : 0;

                $hasil[] = [
                    'unique_id' => $produk->id_produk . '-' . $konv->id_satuan_konversi,
                    'id_produk' => $produk->id_produk,
                    'nama_produk' => $produk->nama_produk,
                    'id_satuan' => $konv->id_satuan_konversi,
                    'nama_satuan' => $konv->satuan->nama_satuan, 
                    'harga_pokok' => $konv->harga_pokok_konversi,
                    // KIRIM DATA STOK KONVERSI
                    'stok_real' => number_format($stok_konv, 2, ',', '.')
                ];
            }
        }

        return response()->json($hasil);
    }

    /**
     * Menyimpan transaksi stok baru
     */
    public function store(Request $request)
    {
        // 1. Bersihkan baris kosong
        $input = $request->all();
        if (isset($input['detail']) && is_array($input['detail'])) {
            $input['detail'] = array_values(array_filter($input['detail'], function($item) {
                return !empty($item['id_produk']);
            }));
        }
        $request->replace($input);

        // 2. Validasi
        $request->validate([
            'id_supplier' => 'nullable|exists:supplier,id_supplier',
            'tanggal_masuk' => 'required|date',
            'keterangan' => 'nullable|string',
            'no_faktur_supplier' => 'nullable|string',
            'detail' => 'required|array|min:1', 
            'detail.*.id_produk' => 'required|string|exists:produk,id_produk',
            'detail.*.jumlah' => 'required|integer|min:1',
            'detail.*.harga_beli_satuan' => 'required|numeric|min:0',
            'detail.*.nama_satuan' => 'required|string', 
        ]);

        try {
            DB::beginTransaction();

            $batch = StokMasukBatch::create([
                'id_user' => Auth::id(), 
                'id_supplier' => $request->id_supplier,
                'tanggal_masuk' => $request->tanggal_masuk,
                'keterangan' => $request->keterangan,
                'no_faktur_supplier' => $request->no_faktur_supplier,
                'total_nilai_faktur' => collect($request->detail)->sum(function($item) {
                    return $item['jumlah'] * $item['harga_beli_satuan'];
                })
            ]);

            foreach ($request->detail as $item) {
                StokMasukDetail::create([
                    'id_batch_stok' => $batch->id_batch_stok,
                    'id_produk' => $item['id_produk'],
                    'jumlah' => $item['jumlah'],
                    'id_satuan' => $item['id_satuan'] ?? null,
                    'satuan' => $item['nama_satuan'],
                    'harga_beli_satuan' => $item['harga_beli_satuan'],
                ]);
            }

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal menyimpan: ' . $e->getMessage());
        }

        return redirect()->route('admin.stok.index')
                         ->with('success', 'Transaksi stok masuk berhasil disimpan.');
    }

    /**
     * Menampilkan halaman EDIT stok masuk
     */
    public function edit(StokMasukBatch $batch)
    {
        $batch->load(['details.produk.satuanDasar', 'details.produk.produkKonversis.satuan']);
        $suppliers = Supplier::orderBy('nama_supplier')->get();
        return view('admin.stok.edit', compact('batch', 'suppliers'));
    }

    /**
     * Memperbarui transaksi stok (UPDATE)
     */
    public function update(Request $request, StokMasukBatch $batch)
    {
        // 1. Bersihkan baris kosong
        $input = $request->all();
        if (isset($input['detail']) && is_array($input['detail'])) {
            $input['detail'] = array_values(array_filter($input['detail'], function($item) {
                return !empty($item['id_produk']);
            }));
        }
        $request->replace($input);

        // 2. Validasi
        $request->validate([
            'id_supplier' => 'nullable|exists:supplier,id_supplier',
            'tanggal_masuk' => 'required|date',
            'keterangan' => 'nullable|string',
            'no_faktur_supplier' => 'nullable|string',
            'detail' => 'required|array|min:1', 
            'detail.*.id_produk' => 'required|string|exists:produk,id_produk',
            'detail.*.jumlah' => 'required|integer|min:1',
            'detail.*.harga_beli_satuan' => 'required|numeric|min:0',
            'detail.*.nama_satuan' => 'required|string', 
        ]);

        try {
            DB::beginTransaction();

            // Update Header
            $batch->update([
                'id_supplier' => $request->id_supplier,
                'tanggal_masuk' => $request->tanggal_masuk,
                'keterangan' => $request->keterangan,
                'no_faktur_supplier' => $request->no_faktur_supplier,
                'id_user_diubah' => Auth::id(), 
                'total_nilai_faktur' => collect($request->detail)->sum(function($item) {
                    return $item['jumlah'] * $item['harga_beli_satuan'];
                })
            ]);

            // Update Detail (Hapus Lama -> Buat Baru)
            $batch->details()->delete();

            foreach ($request->detail as $item) {
                StokMasukDetail::create([
                    'id_batch_stok' => $batch->id_batch_stok,
                    'id_produk' => $item['id_produk'],
                    'jumlah' => $item['jumlah'],
                    'id_satuan' => $item['id_satuan'] ?? null,
                    'satuan' => $item['nama_satuan'],
                    'harga_beli_satuan' => $item['harga_beli_satuan'],
                ]);
            }

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal mengupdate: ' . $e->getMessage());
        }

        return redirect()->route('admin.stok.index')
                         ->with('success', 'Transaksi stok masuk berhasil diperbarui.');
    }
}