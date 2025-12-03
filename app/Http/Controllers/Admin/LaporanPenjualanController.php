<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaksi;
use App\Models\User;
use Illuminate\Http\Request;

class LaporanPenjualanController extends Controller
{
    public function index(Request $request)
    {
        // 1. Siapkan Query Dasar (Hanya Transaksi Selesai)
        $query = Transaksi::with(['kasir', 'details']) // Load relasi
                          ->where('status_pesanan', 'selesai');

        // 2. Terapkan Filter (Jika ada input dari Admin)
        
        // Filter Tanggal
        if ($request->filled('tanggal_mulai') && $request->filled('tanggal_akhir')) {
            $query->whereBetween('tanggal_transaksi', [
                $request->tanggal_mulai, 
                $request->tanggal_akhir
            ]);
        } else {
            // Default: Tampilkan bulan ini saja (agar tidak berat loadingnya)
            $query->whereMonth('tanggal_transaksi', date('m'))
                  ->whereYear('tanggal_transaksi', date('Y'));
        }

        // Filter Kasir
        if ($request->filled('id_kasir') && $request->id_kasir != 'semua') {
            $query->where('id_user_kasir', $request->id_kasir);
        }
        // 4. DEEP SEARCH (PENCARIAN MENDALAM)
        if ($request->filled('search')) {
            $search = $request->search;
            
            $query->where(function($q) use ($search) {
                // A. Cari No Transaksi
                $q->where('id_transaksi', 'like', '%' . $search . '%')
                  
                  // B. Cari Nama Pelanggan
                  ->orWhere('nama_pelanggan', 'like', '%' . $search . '%')
                  
                  // C. Cari Nama Kasir
                  ->orWhere('nama_kasir', 'like', '%' . $search . '%')

                  // D. Cari Nama Barang (Masuk ke Detail -> Produk)
                  ->orWhereHas('details.produk', function($subQuery) use ($search) {
                      $subQuery->where('nama_produk', 'like', '%' . $search . '%');
                  });
            });
        }

        // 3. Hitung Ringkasan (Berdasarkan data yang sudah difilter di atas)
        // Kita clone query agar tidak mengganggu pagination di bawah
        $totalOmzet = $query->clone()->sum('total_harga');
        $totalTransaksi = $query->clone()->count();

        // 4. Ambil Data untuk Tabel (Paginate)
        $laporan = $query->latest('waktu_transaksi')->paginate(10);
        $laporan->appends($request->all());

        // 5. Ambil Daftar Kasir (Untuk Dropdown Filter)
        $listKasir = User::where('role_user', 'kasir')->get();

        return view('admin.laporan.index', compact('laporan', 'totalOmzet', 'totalTransaksi', 'listKasir'));
    }
    public function edit(Request $request)
    {
        // Ambil ID dari ?id=...
        $id = $request->query('id');

        $transaksi = Transaksi::with(['details.produk.satuanDasar', 'details.produk.produkKonversis.satuan'])
                              ->where('id_transaksi', $id)
                              ->firstOrFail();

        return view('admin.laporan.edit', compact('transaksi'));
    }

    /**
     * Simpan Perubahan Transaksi
     */
    public function update(Request $request)
    {
        // Ambil ID dari ?id=...
        $id = $request->query('id');
        
        $transaksi = Transaksi::findOrFail($id);

        $request->validate([
            'total_harga'  => 'required|numeric',
            'bayar'        => 'required|numeric|gte:total_harga',
            'kembalian'    => 'required|numeric',
            'items'        => 'required|array|min:1', 
        ]);

        try {
            \DB::beginTransaction();

            // Update Header
            $transaksi->update([
                'total_harga' => $request->total_harga,
                'bayar'       => $request->bayar,
                'kembalian'   => $request->kembalian,
            ]);

            // Reset Detail
            $transaksi->details()->delete();

            foreach ($request->items as $item) {
                \App\Models\DetailTransaksi::create([
                    'id_transaksi' => $transaksi->id_transaksi,
                    'id_produk'    => $item['id_produk'],
                    'jumlah'       => $item['qty'],
                    'satuan'       => $item['satuan'],
                    'harga_satuan' => $item['harga'],
                    'subtotal'     => $item['subtotal'],
                ]);
            }

            \DB::commit();
            return response()->json(['success' => true, 'message' => 'Transaksi berhasil diperbarui!']);

        } catch (\Exception $e) {
            \DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}