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
use App\Notifications\PesananSelesaiNotification;
use Illuminate\Support\Facades\Mail;
use App\Mail\PesananSelesaiMail;

class TransaksiController extends Controller
{
    public function index(Request $request)
    {
        $pelanggans = User::where('role_user', 'pelanggan')->orderBy('nama')->get();

        // 1. CEK APAKAH ADA TITIPAN ID DARI ADMIN? (Untuk Proses Pesanan Online)
        $draftId = $request->query('id');
        $draftData = null;
        $nextId = null;

        if ($draftId) {
            // Ambil data transaksi online + Relasi untuk hitung stok
            $transaksi = Transaksi::with([
                                    'details.produk.satuanDasar', 
                                    'details.produk.produkKonversis.satuan',
                                    'details.produk.stokMasukDetails', 
                                    'details.produk.detailTransaksis'
                                  ])
                                  ->where('id_transaksi', $draftId)
                                  ->first();

            if ($transaksi && $transaksi->status_pesanan == 'diproses') {
                // Format ulang item agar bisa dibaca oleh JavaScript Kasir
                $items = $transaksi->details->map(function($item) {
                    
                    $produk = $item->produk;
                    
                    // --- PERBAIKAN: HITUNG STOK REALTIME DI SINI ---
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
                    
                    $stok_real = $masuk - $keluar;
                    $stok_formatted = number_format($stok_real, 0, ',', '.');
                    // -----------------------------------------------

                    // Siapkan Opsi Satuan (Lengkap dengan Stok)
                    $opsiSatuan = [];
                    $opsiSatuan[] = [
                        'id'    => $produk->id_satuan_dasar,
                        'nama'  => $satuan_dasar,
                        'harga' => $produk->harga_jual_dasar,
                        'stok'  => $stok_formatted // <--- INI SOLUSINYA
                    ];
                    
                    foreach($produk->produkKonversis as $konv) {
                        $opsiSatuan[] = [
                            'id'    => $konv->id_satuan_konversi,
                            'nama'  => $konv->satuan->nama_satuan,
                            'harga' => $konv->harga_jual_konversi,
                            'stok'  => $stok_formatted
                        ];
                    }

                    // Cari ID Satuan yang terpilih
                    $currentSatuanId = null;
                    foreach($opsiSatuan as $opt) {
                        if ($opt['nama'] == $item->satuan) {
                            $currentSatuanId = $opt['nama']; // Gunakan Nama sebagai ID agar konsisten dengan create.blade
                            break;
                        }
                    }
                    if (!$currentSatuanId && count($opsiSatuan) > 0) {
                        $currentSatuanId = $opsiSatuan[0]['nama'];
                    }

                    return [
                        'id_temp'         => rand(1000,9999),
                        'id_produk_final' => $item->id_produk,
                        'id_produk'       => $item->id_produk,
                        'kode_item'       => $item->id_produk,
                        'nama_barang'     => $produk->nama_produk,
                        'qty'             => $item->jumlah,
                        'id_satuan'       => $currentSatuanId,
                        'satuan'          => $item->satuan,
                        'harga'           => $item->harga_satuan,
                        'subtotal'        => $item->subtotal,
                        'opsi_satuan'     => $opsiSatuan,
                    ];
                });

                $draftData = [
                    'id_transaksi'   => $transaksi->id_transaksi,
                    'pelanggan_id'   => $transaksi->id_user_pelanggan,
                    'nama_pelanggan' => $transaksi->nama_pelanggan,
                    'items'          => $items
                ];

                $nextId = $transaksi->id_transaksi;
            }
        } 
        
        if (!$nextId) {
            $today = date('ymd');
            $count = Transaksi::whereDate('created_at', date('Y-m-d'))->count() + 1;
            $random = str_pad($count, 3, '0', STR_PAD_LEFT) . rand(10, 99);
            $nextId = "TRX-{$today}-{$random}";
        }

        return view('kasir.transaksi.create', compact('nextId', 'pelanggans', 'draftData'));
    }

    // API Cari Produk (Saya biarkan sesuai punya Anda, sudah benar)
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
        $request->validate([
            'id_transaksi' => 'required',
            'total_harga'  => 'required|numeric',
            'bayar'        => 'required|numeric|gte:total_harga',
            'items'        => 'required|array|min:1', 
        ]);

        try {
            DB::beginTransaction();

            // Cek Update atau Baru
            $transaksi = Transaksi::where('id_transaksi', $request->id_transaksi)->first();

            if ($transaksi) {
                // UPDATE PESANAN ONLINE
                $transaksi->update([
                    'id_user_kasir'     => Auth::id(),
                    'nama_kasir'        => Auth::user()->nama,
                    'total_harga'       => $request->total_harga,
                    'bayar'             => $request->bayar,
                    'kembalian'         => $request->kembalian,
                    'status_pesanan'    => 'selesai',
                    
                    // Update Waktu
                    'waktu_transaksi'   => now(),
                    'tanggal_transaksi' => date('Y-m-d'),
                    'metode_bayar'      => 'cash', 
                ]);
            } else {
                // BARU (OFFLINE)
                $transaksi = Transaksi::create([
                    'id_transaksi'      => $request->id_transaksi,
                    'id_user_kasir'     => Auth::id(),
                    'nama_kasir'        => Auth::user()->nama,
                    'id_user_pelanggan' => $request->id_pelanggan, 
                    'nama_pelanggan'    => $request->nama_pelanggan ?? 'UMUM',
                    'waktu_transaksi'   => now(),
                    'tanggal_transaksi' => date('Y-m-d'),
                    'total_harga'       => $request->total_harga,
                    'bayar'             => $request->bayar,
                    'kembalian'         => $request->kembalian,
                    'jenis_transaksi'   => 'offline',
                    'status_pesanan'    => 'selesai',
                    'metode_bayar'      => 'cash',
                ]);
            }

            // Timpa Detail
            $transaksi->details()->delete();

            foreach ($request->items as $item) {
                DetailTransaksi::create([
                    'id_transaksi' => $transaksi->id_transaksi,
                    'id_produk'    => $item['id_produk'],
                    'jumlah'       => $item['qty'],
                    'satuan'       => $item['satuan'],
                    'harga_satuan' => $item['harga'],
                    'subtotal'     => $item['subtotal'],
                ]);
            }

            DB::commit();

            // Notifikasi (Email & Web)
            if ($transaksi->pelanggan) {
                try {
                     $transaksi->pelanggan->notify(new PesananSelesaiNotification($transaksi));
                } catch (\Exception $e) {}

                if ($transaksi->pelanggan->email) {
                    try {
                        Mail::to($transaksi->pelanggan->email)->send(new PesananSelesaiMail($transaksi));
                    } catch (\Exception $e) {}
                }
            }

            return response()->json([
                'success' => true, 
                'message' => 'Transaksi berhasil!', 
                'id_transaksi' => $transaksi->id_transaksi
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function riwayat(Request $request)
    {
        $query = Transaksi::with('kasir')
                          ->where('status_pesanan', 'selesai')
                          ->orderBy('created_at', 'desc'); 

        if ($request->filled('tanggal_mulai') && $request->filled('tanggal_akhir')) {
            $query->whereBetween('tanggal_transaksi', [$request->tanggal_mulai, $request->tanggal_akhir]);
        } else {
            $duaHariLalu = \Carbon\Carbon::now()->subDays(1)->format('Y-m-d');
            $query->whereDate('tanggal_transaksi', '>=', $duaHariLalu);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('id_transaksi', 'like', '%' . $search . '%')
                  ->orWhere('nama_pelanggan', 'like', '%' . $search . '%');
            });
        }

        $riwayat = $query->paginate(10);
        $riwayat->appends($request->all());

        if ($request->ajax()) {
            return response()->json([
                'html' => view('kasir.transaksi.riwayat_body', compact('riwayat'))->render(),
                'next_page_url' => $riwayat->nextPageUrl()
            ]);
        }

        return view('kasir.transaksi.riwayat', compact('riwayat'));
    }

    public function show(Request $request)
    {
        $id = $request->query('id');
        if (!$id) return response()->json(['error' => 'ID tidak ditemukan'], 404);

        $transaksi = Transaksi::with(['details.produk', 'kasir'])->where('id_transaksi', $id)->firstOrFail();
        return response()->json($transaksi);
    }

    public function cetak(Request $request)
    {
        $id = $request->query('id');
        $transaksi = Transaksi::with(['details.produk', 'kasir'])->where('id_transaksi', $id)->firstOrFail();
        return view('kasir.transaksi.struk', compact('transaksi'));
    }
}