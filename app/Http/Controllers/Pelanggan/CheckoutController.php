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
    public function index()
    {
        $keranjang = Keranjang::with('produk')->where('id_user', Auth::id())->get();

        if ($keranjang->isEmpty()) {
            return redirect()->route('home')->with('error', 'Keranjang Anda kosong.');
        }

        return view('pelanggan.checkout.index', compact('keranjang'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'alamat_pengiriman' => 'required|string|max:500',
            'metode_bayar'      => 'required|in:cash,transfer',
            'catatan'           => 'nullable|string|max:255',
        ]);

        try {
            DB::beginTransaction();

            $user = Auth::user();
            $keranjang = Keranjang::where('id_user', $user->id_user)->get();
            $totalHarga = $keranjang->sum(fn($item) => $item->harga_saat_ini * $item->jumlah);

            // 1. Generate ID Transaksi (TRX-ONLINE-...)
            $today = date('ymd');
            $count = Transaksi::whereDate('created_at', date('Y-m-d'))->count() + 1;
            $id_transaksi = "TRX-OL-{$today}-" . str_pad($count, 4, '0', STR_PAD_LEFT);

            // 2. Buat Transaksi Header
            $transaksi = Transaksi::create([
                'id_transaksi'      => $id_transaksi,
                'id_user_pelanggan' => $user->id_user,
                'nama_pelanggan'    => $user->nama,
                'nama_kasir'        => 'System (Online)', // Default sistem
                'waktu_transaksi'   => now(),
                'tanggal_transaksi' => date('Y-m-d'),
                'total_harga'       => $totalHarga,
                'bayar'             => 0, // Belum bayar (COD/Transfer nanti)
                'kembalian'         => 0,
                'jenis_transaksi'   => 'online',
                'status_pesanan'    => 'diproses', // Status awal pesanan online
                'metode_bayar'      => $request->metode_bayar,
                'keterangan'        => $request->alamat_pengiriman . ' | Catatan: ' . $request->catatan, // Simpan alamat di keterangan atau buat kolom baru (kita pakai keterangan dulu)
            ]);

            // 3. Pindahkan Item Keranjang ke Detail Transaksi
            foreach ($keranjang as $item) {
                DetailTransaksi::create([
                    'id_transaksi' => $id_transaksi,
                    'id_produk'    => $item->id_produk,
                    'jumlah'       => $item->jumlah,
                    'satuan'       => $item->satuan,
                    'id_satuan'    => null, // Bisa diisi jika logic keranjang simpan ID
                    'harga_satuan' => $item->harga_saat_ini,
                    'subtotal'     => $item->jumlah * $item->harga_saat_ini,
                ]);
            }

            // 4. Kosongkan Keranjang
            Keranjang::where('id_user', $user->id_user)->delete();

            DB::commit();

            return redirect()->route('home')->with('success', 'Pesanan berhasil dibuat! ID: ' . $id_transaksi);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}