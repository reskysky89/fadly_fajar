<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Produk;
use App\Models\Transaksi;
use App\Models\Supplier;
use App\Models\DetailTransaksi; // <--- Pastikan Model ini di-import
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // ==========================================
        // 1. STATISTIK HARI INI
        // ==========================================
        $hariIni = Carbon::today();
        
        // A. OMSET
        $omsetHariIni = Transaksi::whereDate('tanggal_transaksi', $hariIni)
                         ->where('status_pesanan', 'selesai')
                         ->sum('total_harga');

        // B. JUMLAH TRANSAKSI
        $transaksiHariIni = Transaksi::whereDate('tanggal_transaksi', $hariIni)
                            ->where('status_pesanan', 'selesai')
                            ->count();

        // C. KEUNTUNGAN (Looping PHP Multi Satuan)
        $keuntunganHariIni = 0;
        $transaksiSelesai = Transaksi::with(['details.produk.produkKonversis.satuan', 'details.produk.satuanDasar'])
                            ->whereDate('tanggal_transaksi', $hariIni)
                            ->where('status_pesanan', 'selesai')
                            ->get();

        foreach ($transaksiSelesai as $trx) {
            foreach ($trx->details as $detail) {
                $produk = $detail->produk;
                if (!$produk) continue;

                $modalPerSatuan = $produk->harga_pokok_dasar;
                if ($produk->produkKonversis) {
                    foreach ($produk->produkKonversis as $konversi) {
                        if ($konversi->satuan && $konversi->satuan->nama_satuan == $detail->satuan) {
                            $modalPerSatuan = $konversi->harga_pokok_konversi;
                            break; 
                        }
                    }
                }
                $totalModal = $modalPerSatuan * $detail->jumlah;
                $profitItem = $detail->subtotal - $totalModal;
                $keuntunganHariIni += $profitItem;
            }
        }

        // ==========================================
        // 2. GRAFIK DENYUT NADI
        // ==========================================
        $chartData = [];
        $chartLabels = [];
        
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $total = Transaksi::whereDate('tanggal_transaksi', $date)
                              ->where('status_pesanan', 'selesai')
                              ->sum('total_harga');
            
            $chartLabels[] = $date->format('d M'); 
            $chartData[] = $total;
        }

        // ==========================================
        // 3. LIST SUPPLIER
        // ==========================================
        $suppliers = Supplier::orderBy('nama_supplier')->get();


        // ==========================================
        // 4. TOP PRODUK GLOBAL (DASHBOARD)
        // ==========================================
        // Menggunakan 'subtotal' sebagai pembobot ranking agar aman dari satuan DUS/PCS
        $produkTerlaris = DB::table('detail_transaksi')
            ->join('produk', 'detail_transaksi.id_produk', '=', 'produk.id_produk')
            ->join('transaksi', 'detail_transaksi.id_transaksi', '=', 'transaksi.id_transaksi')
            ->select(
                'produk.nama_produk',
                DB::raw('SUM(detail_transaksi.jumlah) as qty_mentah'),
                // FIX 1: Menggunakan 'subtotal' agar tidak error column not found
                DB::raw('SUM(detail_transaksi.subtotal) as nilai_volume') 
            )
            ->where('transaksi.status_pesanan', 'selesai')
            ->groupBy('produk.id_produk', 'produk.nama_produk')
            ->orderByDesc('nilai_volume')
            ->limit(5)
            ->get();

        $totalVolumeTop5 = $produkTerlaris->sum('nilai_volume');


        // ==========================================
        // 5. PRODUK ZOMBIE GLOBAL (30 HARI TERAKHIR)
        // ==========================================
        $batasZombie = 10; 
        
        $produkZombie = DB::table('produk')
            ->leftJoin('detail_transaksi', function($join) {
                $join->on('produk.id_produk', '=', 'detail_transaksi.id_produk')
                     ->join('transaksi', 'detail_transaksi.id_transaksi', '=', 'transaksi.id_transaksi')
                     ->whereRaw('transaksi.tanggal_transaksi >= DATE_SUB(NOW(), INTERVAL 30 DAY)')
                     ->where('transaksi.status_pesanan', 'selesai');
            })
            ->select(
                'produk.nama_produk',
                // FIX 2: Menggunakan 'id_detail' (Primary Key DetailTransaksi)
                DB::raw('COUNT(detail_transaksi.id_detail) as frekuensi_transaksi')
            )
            ->groupBy('produk.id_produk', 'produk.nama_produk')
            ->having('frekuensi_transaksi', '<', $batasZombie)
            ->orderBy('frekuensi_transaksi', 'asc')
            ->limit(10)
            ->get();


        // RETURN VIEW
        return view('admin.dashboard', compact(
            'omsetHariIni', 'transaksiHariIni', 'keuntunganHariIni', 
            'suppliers', 'chartData', 'chartLabels',
            'produkTerlaris', 'totalVolumeTop5', 'produkZombie'
        ));
    }

    // ====================================================================
    // API SAKTI: ANALISA SUPPLIER (Top Produk Pcs & Zombie Stock)
    // ====================================================================
    public function getSupplierAnalysis(Request $request)
    {
        $id_supplier = $request->query('id_supplier');
        if (!$id_supplier) return response()->json(['top' => [], 'zombie' => []]);

        try {
            // --------------------------------------------------------
            // A. TOP PRODUK (Dengan Logika Konversi Satuan Otomatis)
            // --------------------------------------------------------
            
            // 1. Ambil data detail transaksi (Eager Loading Relasi)
            $transaksiSupplier = DetailTransaksi::with([
                    'transaksi', 
                    'produk.satuanDasar', 
                    'produk.produkKonversis.satuan'
                ])
                ->whereHas('transaksi', function($q) {
                    $q->where('status_pesanan', 'selesai');
                    
                    $q->whereDate('tanggal_transaksi', '>=', now()->subDays(7));
                })
                ->whereHas('produk', function($q) use ($id_supplier) {
                    $q->where('id_supplier', $id_supplier);
                })
                ->get();

            // 2. Variable penampung
            $hasilHitung = [];

            foreach ($transaksiSupplier as $detail) {
                // Skip jika produk sudah dihapus
                if (!$detail->produk) continue; 
                
                $id = $detail->produk->id_produk;
                
                // Buat entry baru di array jika belum ada
                if (!isset($hasilHitung[$id])) {
                    $hasilHitung[$id] = [
                        'nama_produk' => $detail->produk->nama_produk,
                        'total_pcs' => 0
                    ];
                }

                // --- INTINYA DISINI (Hitung Konversi) ---
                $qty = $detail->jumlah;          
                $satuanNota = $detail->satuan;   // Misal: "BAL"
                $pengali = 1;                    // Default 1 (Pcs)

                $satuanDasar = optional($detail->produk->satuanDasar)->nama_satuan ?? 'PCS';

                // Jika satuan di nota ("BAL") BEDA dengan dasar ("PCS")
                if (strtoupper($satuanNota) !== strtoupper($satuanDasar)) {
                    // Cari di tabel konversi produk tersebut
                    $dataKonversi = $detail->produk->produkKonversis->first(function ($k) use ($satuanNota) {
                        return $k->satuan && strtoupper($k->satuan->nama_satuan) === strtoupper($satuanNota);
                    });

                    // Jika ketemu (BAL = 200), set pengali
                    if ($dataKonversi) {
                        $pengali = $dataKonversi->nilai_konversi;
                    }
                }

                // Masukkan ke total (Qty * Pengali)
                $hasilHitung[$id]['total_pcs'] += ($qty * $pengali);
            }

            // 3. Urutkan dan Ambil Top 5
            $topProduk = collect($hasilHitung)
                ->sortByDesc('total_pcs') // Terbanyak Pcs
                ->take(5)
                ->values()
                ->map(function($item) {
                    return [
                        'nama_produk' => $item['nama_produk'],
                        'total_qty'   => $item['total_pcs']
                    ];
                });

            // --------------------------------------------------------
            // B. PRODUK ZOMBIE (Logic Stok Realtime)
            // --------------------------------------------------------
            $semuaProduk = Produk::with(['stokMasukDetails', 'detailTransaksis.transaksi', 'satuanDasar', 'produkKonversis.satuan'])
                ->where('id_supplier', $id_supplier)
                ->get();

            $zombieList = [];

            foreach ($semuaProduk as $p) {
                // 1. HITUNG STOK REALTIME (Masuk - Keluar)
                $satuan_dasar = $p->satuanDasar->nama_satuan ?? 'PCS';
                
                // Hitung Masuk
                $masuk = 0;
                if ($p->stokMasukDetails) {
                    foreach ($p->stokMasukDetails as $d) {
                        $jml = $d->jumlah;
                        if ($d->satuan !== $satuan_dasar && $p->produkKonversis) {
                            $konv = $p->produkKonversis->firstWhere('satuan.nama_satuan', $d->satuan);
                            if ($konv) $jml *= $konv->nilai_konversi;
                        }
                        $masuk += $jml;
                    }
                }

                // Hitung Keluar
                $keluar = 0;
                $transaksiTerakhir = null;
                
                if ($p->detailTransaksis) {
                    foreach ($p->detailTransaksis as $d) {
                        // Hanya hitung transaksi yang SELESAI
                        if($d->transaksi && $d->transaksi->status_pesanan == 'selesai'){
                            $jml = $d->jumlah;
                            if ($d->satuan !== $satuan_dasar && $p->produkKonversis) {
                                $konv = $p->produkKonversis->firstWhere('satuan.nama_satuan', $d->satuan);
                                if ($konv) $jml *= $konv->nilai_konversi;
                            }
                            $keluar += $jml;

                            // Cek tanggal
                            $tgl = Carbon::parse($d->transaksi->tanggal_transaksi);
                            if (!$transaksiTerakhir || $tgl->gt($transaksiTerakhir)) {
                                $transaksiTerakhir = $tgl;
                            }
                        }
                    }
                }

                $stokReal = $masuk - $keluar;

                // 2. CEK STATUS ZOMBIE
                // Syarat: Stok > 0 TAPI (Belum pernah laku ATAU Tidak laku > 60 hari)
                if ($stokReal > 0) {
                    $isZombie = false;
                    
                    if (!$transaksiTerakhir) {
                        $isZombie = true; // Barang baru tapi gak laku-laku
                    } else {
                        if ($transaksiTerakhir->diffInDays(Carbon::now()) > 30) {
                            $isZombie = true; // Barang lama mandeg
                        }
                    }

                    if ($isZombie) {
                        $zombieList[] = [
                            'nama_produk' => $p->nama_produk,
                            'stok' => $stokReal
                        ];
                    }
                }
            }

            // Ambil 5 Zombie
            $zombieProduk = array_slice($zombieList, 0, 5);

            return response()->json([
                'top' => $topProduk,
                'zombie' => $zombieProduk
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Server Error: ' . $e->getMessage()], 500);
        }
    }
}