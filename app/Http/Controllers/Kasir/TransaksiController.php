<?php

namespace App\Http\Controllers\Kasir;

use App\Http\Controllers\Controller;
use App\Models\Produk;
use App\Models\Transaksi;
use App\Models\DetailTransaksi;
use App\Models\User; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TransaksiController extends Controller
{
    public function index()
    {
        // Generate ID Transaksi Otomatis (Format: TRX-Tanggal-Random)
        // Contoh: TRX-251125-001
        $today = date('ymd');
        $random = rand(100, 999);
        $nextId = "TRX-{$today}-{$random}";
        $pelanggans = User::where('role_user', 'pelanggan')->orderBy('nama')->get();

        return view('kasir.transaksi.create', compact('nextId','pelanggans'));
    }

    // API untuk pencarian produk di kasir (Scan Barcode)
    public function cariProduk(Request $request)
    {
        $search = $request->query('search');
        if (empty($search)) return response()->json([]);

        // 1. Cari Produk (Load semua relasi yang dibutuhkan untuk hitung stok)
        $produks = Produk::where('status_produk', 'aktif')
                         ->where(function($q) use ($search) {
                             $q->where('nama_produk', 'like', '%' . $search . '%')
                               ->orWhere('id_produk', 'like', '%' . $search . '%');
                         })
                         ->with(['satuanDasar', 'produkKonversis.satuan', 'stokMasukDetails', 'detailTransaksis']) 
                         ->limit(5)
                         ->get();

        $hasil = [];
        foreach ($produks as $produk) {
            
            // --- LOGIKA HITUNG STOK (Sama persis dengan Admin) ---
            $total_stok_masuk_pcs = 0;
            
            // A. Hitung Stok Masuk (Konversi ke Satuan Dasar)
            foreach ($produk->stokMasukDetails as $stokMasuk) {
                $jumlah = $stokMasuk->jumlah;
                $satuan_input = $stokMasuk->satuan;
                
                // Jika satuannya BUKAN satuan dasar (misal: DUS), kalikan!
                if ($satuan_input !== ($produk->satuanDasar->nama_satuan ?? 'PCS')) {
                    $konversi = $produk->produkKonversis->first(function ($konv) use ($satuan_input) {
                        return $konv->satuan->nama_satuan === $satuan_input;
                    });
                    
                    if ($konversi) {
                        $jumlah = $jumlah * $konversi->nilai_konversi;
                    }
                }
                $total_stok_masuk_pcs += $jumlah;
            }

            // B. Hitung Stok Keluar (Penjualan)
            // Asumsi: detail_transaksi menyimpan jumlah dalam satuan yang dipilih, 
            // jadi kita harus konversi juga jika penjualan dalam DUS.
            // (Untuk sederhananya saat ini kita anggap penjualan mengurangi stok dasar secara proporsional atau langsung)
            // TAPI IDEALNYA: Kita harus cek satuan di tabel detail_transaksi juga.
            // Mari kita buat logika sederhana dulu: sum('jumlah') 
            // *Catatan: Jika Anda menjual dalam DUS, pastikan detail_transaksi menyimpan konversinya atau kita hitung di sini.
            // Untuk sekarang kita pakai sum sederhana, bisa disempurnakan nanti.
            $total_stok_keluar_pcs = 0;
             foreach ($produk->detailTransaksis as $terjual) {
                $jumlah_jual = $terjual->jumlah;
                $satuan_jual = $terjual->satuan;
                 if ($satuan_jual !== ($produk->satuanDasar->nama_satuan ?? 'PCS')) {
                    $konversi = $produk->produkKonversis->first(function ($konv) use ($satuan_jual) {
                        return $konv->satuan->nama_satuan === $satuan_jual;
                    });
                    if ($konversi) {
                        $jumlah_jual = $jumlah_jual * $konversi->nilai_konversi;
                    }
                }
                $total_stok_keluar_pcs += $jumlah_jual;
             }
            
            // C. Stok Akhir (Dalam Satuan Dasar / PCS)
            $stok_saat_ini_pcs = $total_stok_masuk_pcs - $total_stok_keluar_pcs;
            // -----------------------------------------------------


            // 2. Format Hasil untuk Pilihan Satuan Dasar (PCS)
            $hasil[] = [
                'unique_id' => $produk->id_produk . '-' . $produk->id_satuan_dasar,
                'id_produk' => $produk->id_produk,
                'nama_produk' => $produk->nama_produk,
                'id_satuan' => $produk->id_satuan_dasar,
                'nama_satuan' => $produk->satuanDasar->nama_satuan, 
                'harga_jual' => $produk->harga_jual_dasar,
                // Tampilkan stok dalam satuan dasar
                'stok_real' => number_format($stok_saat_ini_pcs, 0, ',', '.')
            ];

            // 3. Format Hasil untuk Pilihan Satuan Konversi (DUS)
            foreach ($produk->produkKonversis as $konv) {
                // Hitung stok dalam satuan konversi (Misal: 400 PCS / 40 = 10 DUS)
                $stok_konversi = ($konv->nilai_konversi > 0) ? ($stok_saat_ini_pcs / $konv->nilai_konversi) : 0;

                $hasil[] = [
                    'unique_id' => $produk->id_produk . '-' . $konv->id_satuan_konversi,
                    'id_produk' => $produk->id_produk,
                    'nama_produk' => $produk->nama_produk,
                    'id_satuan' => $konv->id_satuan_konversi,
                    'nama_satuan' => $konv->satuan->nama_satuan, 
                    'harga_jual' => $konv->harga_jual_konversi,
                    // Tampilkan stok dalam satuan konversi (misal: 10)
                    'stok_real' => number_format($stok_konversi, 2, ',', '.')
                ];
            }
        }
        
        return response()->json($hasil);
    }
    public function store(Request $request)
    {
        // 1. Validasi Input
        $request->validate([
            'total_harga'  => 'required|numeric',
            'bayar'        => 'required|numeric|gte:total_harga', // Bayar harus >= Total
            'kembalian'    => 'required|numeric',
            'items'        => 'required|array|min:1', // Harus ada barang
            'items.*.id_produk' => 'required|exists:produk,id_produk',
            'items.*.qty'       => 'required|integer|min:1',
            'items.*.satuan'    => 'required|string',
        ]);

        try {
            DB::beginTransaction();

            // 2. GENERATE ID TRANSAKSI KHUSUS (Format: 0001/KSR/1125)
            // Hitung jumlah transaksi bulan ini untuk nomor urut
            $count = Transaksi::whereMonth('waktu_transaksi', date('m'))
                              ->whereYear('waktu_transaksi', date('Y'))
                              ->count();
            
            $no_urut = str_pad($count + 1, 4, '0', STR_PAD_LEFT); // 0001, 0002, dst
            $bulanTahun = date('my'); // 1125 (Nov 2025)
            $id_transaksi_fix = "{$no_urut}/KSR/{$bulanTahun}";

            // 3. Simpan Header Transaksi
            $transaksi = Transaksi::create([
                'id_transaksi'      => $id_transaksi_fix,
                'id_user_kasir'     => Auth::id(),
                'id_user_pelanggan' => null, 
                'nama_kasir'        => Auth::user()->nama,
                'nama_pelanggan'    => 'UMUM', 
                
                // --- PERBAIKAN DI SINI ---
                'tanggal_transaksi' => date('Y-m-d'), // Isi tanggal hari ini
                'waktu_transaksi'   => now(),         // Isi waktu lengkap
                // -------------------------

                'total_harga'       => $request->total_harga,
                'bayar'             => $request->bayar,
                'kembalian'         => $request->kembalian,
                'jenis_transaksi'   => 'offline',
                'tipe_transaksi'    => 'penjualan',
                'status_pesanan'    => 'selesai',
                'metode_bayar'      => 'cash',
            ]);

            // 4. Simpan Detail Transaksi (Barang-barangnya)
            // INILAH YANG AKAN MENGURANGI STOK SECARA OTOMATIS DI SISTEM
            foreach ($request->items as $item) {
                DetailTransaksi::create([
                    'id_transaksi' => $transaksi->id_transaksi,
                    'id_produk'    => $item['id_produk'],
                    'jumlah'       => $item['qty'],
                    'satuan'       => $item['satuan'], // Simpan satuan (PCS/DUS)
                    'harga_satuan' => $item['harga'],
                    'subtotal'     => $item['subtotal'],
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil!',
                'id_transaksi' => $transaksi->id_transaksi
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan: ' . $e->getMessage()
            ], 500);
        }
    }
    public function riwayat(Request $request)
    {
        $query = Transaksi::with('kasir')
                    ->where('tipe_transaksi', 'penjualan')
                    ->where('status_pesanan', 'selesai')
                    ->orderBy('created_at', 'desc'); 

        // --- LOGIKA FILTER TANGGAL ---
        
        // A. Jika User Memilih Tanggal Sendiri (Bisa lihat data lama)
        if ($request->filled('tanggal_mulai') && $request->filled('tanggal_akhir')) {
            $query->whereBetween('tanggal_transaksi', [
                $request->tanggal_mulai, 
                $request->tanggal_akhir
            ]);
        } 
        // B. DEFAULT: Jika tidak ada filter, hanya tampilkan 2x24 Jam (2 Hari Terakhir)
        else {
            $duaHariLalu = \Carbon\Carbon::now()->subDays(1)->format('Y-m-d');
            // Ambil transaksi dari 2 hari lalu sampai sekarang
            $query->whereDate('tanggal_transaksi', '>=', $duaHariLalu);
        }
        // -----------------------------

        // Filter Pencarian (ID Transaksi) - Tetap bisa mencari di luar tanggal default
        if ($request->filled('search')) {
            // Jika sedang mencari ID tertentu, kita abaikan batasan tanggal default
            // agar riwayat lama pun bisa ketemu jika ID-nya diketik
            $query->orWhere('id_transaksi', 'like', '%' . $request->search . '%');
        }

        $riwayat = $query->paginate(10);
        $riwayat->appends($request->all());

        // AJAX Response (Infinite Scroll)
        if ($request->ajax()) {
            return response()->json([
                'html' => view('kasir.transaksi.riwayat_body', compact('riwayat'))->render(),
                'next_page_url' => $riwayat->nextPageUrl()
            ]);
        }

        return view('kasir.transaksi.riwayat', compact('riwayat'));
    }
    // Ambil Detail Transaksi (JSON untuk Modal)
    public function show(Request $request)
    {
        // Ambil ID dari parameter ?id=...
        $id = $request->query('id'); 
        
        if (!$id) return response()->json(['error' => 'ID tidak ditemukan'], 404);

        $transaksi = Transaksi::with(['details.produk', 'kasir'])->where('id_transaksi', $id)->firstOrFail();
        return response()->json($transaksi);
    }

    // Cetak Struk (Tampilan HTML Khusus Print)
   public function cetak(Request $request)
    {
        // Ambil ID dari parameter ?id=...
        $id = $request->query('id');

        $transaksi = Transaksi::with(['details.produk', 'kasir'])->where('id_transaksi', $id)->firstOrFail();
        return view('kasir.transaksi.struk', compact('transaksi'));
    }
    
    // Fungsi Store akan kita buat setelah View selesai
}