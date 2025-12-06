<?php

namespace App\Http\Controllers\Pelanggan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Transaksi;
use App\Models\Keranjang;
use Illuminate\Support\Facades\Auth;

class RiwayatPesananController extends Controller
{
    public function index()
    {
        // Ambil transaksi milik user, urutkan dari yang BARU DIBUAT
        $riwayat = Transaksi::with(['details.produk'])
                            ->where('id_user_pelanggan', Auth::id())
                            ->where('jenis_transaksi', 'online')
                            
                            // PERBAIKAN: Urutkan by Created At (Waktu Masuk)
                            ->orderBy('created_at', 'desc') 
                            ->get();

        return view('pelanggan.riwayat.index', compact('riwayat'));
    }

    // Fungsi Batalkan Pesanan oleh Pelanggan
    public function batalkanPesanan($id)
    {
        $transaksi = Transaksi::where('id_transaksi', $id)
                              ->where('id_user_pelanggan', Auth::id()) // Pastikan punya sendiri
                              ->firstOrFail();

        // Validasi: Hanya boleh batal jika status masih 'diproses'
        if ($transaksi->status_pesanan !== 'diproses') {
            return back()->with('error', 'Pesanan tidak bisa dibatalkan karena sudah diproses/selesai.');
        }

        // Update Status Jadi Batal
        $transaksi->update(['status_pesanan' => 'batal']);

        // Opsional: Jika stok sudah terpotong saat checkout, kembalikan stok disini.
        // Tapi berdasarkan logika kita sebelumnya (Stok terpotong saat Admin klik Selesai),
        // maka disini cukup ubah status saja.

        return back()->with('success', 'Pesanan berhasil dibatalkan.');
    }
}