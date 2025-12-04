<?php

namespace App\Http\Controllers\Pelanggan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Keranjang;
use App\Models\Transaksi;
use App\Models\DetailTransaksi;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CheckoutController extends Controller
{
    // 1. Tampilkan Halaman Checkout
    public function index()
    {
        $userId = Auth::id();
        // Ambil keranjang user
        $keranjang = Keranjang::with('produk')->where('id_user', $userId)->get();

        // Jika keranjang kosong, tendang ke home
        if ($keranjang->isEmpty()) {
            return redirect()->route('home')->with('error', 'Keranjang belanja Anda kosong.');
        }

        return view('pelanggan.checkout.index', compact('keranjang'));
    }

    // 2. Proses Simpan Pesanan
    public function store(Request $request)
    {
        // 1. Validasi Dinamis
        $rules = [
            'metode_bayar'      => 'required|in:cash,transfer',
            'metode_pengiriman' => 'required|in:diantar,ambil_sendiri', // <-- Validasi Baru
            'catatan'           => 'nullable|string|max:255',
        ];

        // Jika Diantar, Alamat Wajib. Jika Ambil Sendiri, Alamat Boleh Kosong.
        if ($request->metode_pengiriman == 'diantar') {
            $rules['alamat_pengiriman'] = 'required|string|max:500';
        } else {
            $rules['alamat_pengiriman'] = 'nullable|string';
        }

        $request->validate($rules);

        try {
            DB::beginTransaction();
            $user = Auth::user();
            $keranjang = Keranjang::where('id_user', $user->id_user)->get();
            $totalBelanja = $keranjang->sum(fn($item) => $item->harga_saat_ini * $item->jumlah);
            
            // Generate ID
            $today = date('ymd');
            $count = Transaksi::whereDate('created_at', date('Y-m-d'))->count() + 1;
            $id_transaksi = "TRX-OL-{$today}-" . str_pad($count, 4, '0', STR_PAD_LEFT);

            // Tentukan Alamat Final
            $alamatFinal = $request->metode_pengiriman == 'diantar' 
                ? $request->alamat_pengiriman 
                : 'PELANGGAN AMBIL SENDIRI DI TOKO';

            // Simpan Header
            $transaksi = Transaksi::create([
                'id_transaksi'      => $id_transaksi,
                'id_user_pelanggan' => $user->id_user,
                'nama_pelanggan'    => $user->nama,
                'nama_kasir'        => 'System (Online)',
                'waktu_transaksi'   => now(),
                'tanggal_transaksi' => date('Y-m-d'),
                'total_harga'       => $totalBelanja,
                'bayar'             => 0,
                'kembalian'         => 0,
                'jenis_transaksi'   => 'online',
                'status_pesanan'    => 'diproses',
                
                // --- DATA BARU ---
                'metode_bayar'      => $request->metode_bayar,
                'metode_pengiriman' => $request->metode_pengiriman, // Simpan Pilihan
                // -----------------

                'keterangan'        => "Pengiriman: " . strtoupper(str_replace('_', ' ', $request->metode_pengiriman)) . " | Alamat: $alamatFinal | Catatan: {$request->catatan}",
            ]);

            // ... (Logika simpan detail barang SAMA SEPERTI SEBELUMNYA, tidak berubah) ...
            foreach ($keranjang as $item) {
                DetailTransaksi::create([
                    'id_transaksi' => $id_transaksi,
                    'id_produk'    => $item->id_produk,
                    'jumlah'       => $item->jumlah,
                    'satuan'       => $item->satuan,
                    'harga_satuan' => $item->harga_saat_ini,
                    'subtotal'     => $item->jumlah * $item->harga_saat_ini,
                ]);
            }
            
            Keranjang::where('id_user', $user->id_user)->delete();
            DB::commit();

            return redirect()->route('pelanggan.riwayat')->with('success', 'Pesanan berhasil dibuat!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}