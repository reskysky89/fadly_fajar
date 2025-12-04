<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Transaksi;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PesananOnlineController extends Controller
{
    public function index()
    {
        // 1. Ambil Pesanan Baru (Status: diproses)
        $pesananBaru = Transaksi::with(['pelanggan', 'details.produk'])
                                ->where('jenis_transaksi', 'online')
                                ->where('status_pesanan', 'diproses')
                                ->latest('waktu_transaksi')
                                ->get();

        // 2. Ambil Riwayat Selesai (Status: selesai/batal)
        $riwayatSelesai = Transaksi::with(['pelanggan', 'kasir'])
                                   ->where('jenis_transaksi', 'online')
                                   ->whereIn('status_pesanan', ['selesai', 'batal'])
                                   ->latest('waktu_transaksi')
                                   ->paginate(10); // Pagination biar ringan

        return view('admin.pesanan.index', compact('pesananBaru', 'riwayatSelesai'));
    }

    // Fungsi untuk Menyelesaikan Pesanan (Terima Bayaran)
    public function selesaikan(Request $request, $id)
    {
        $request->validate([
            'bayar' => 'required|numeric',
        ]);

        $transaksi = Transaksi::where('id_transaksi', $id)->firstOrFail();

        if ($request->bayar < $transaksi->total_harga) {
            return back()->with('error', 'Uang pembayaran kurang!');
        }

        try {
            DB::beginTransaction();

            $transaksi->update([
                'status_pesanan' => 'selesai',
                'id_user_kasir'  => Auth::id(), // Siapa yang memproses (Admin/Kasir)
                'nama_kasir'     => Auth::user()->nama,
                'bayar'          => $request->bayar,
                'kembalian'      => $request->bayar - $transaksi->total_harga,
                'waktu_transaksi'=> now(), // Update waktu jadi waktu pembayaran
            ]);

            // Catatan: Stok sebenarnya sudah kita potong di 'stok_ready' view, 
            // tapi untuk memastikan data konsisten, saat 'diproses' stok belum terpotong di tabel stok_keluar secara permanen?
            // Sesuai logika kita sebelumnya (Realtime Stok), stok dihitung dari (Masuk - Keluar).
            // DetailTransaksi sudah tersimpan saat checkout, jadi stok SUDAH TERPOTONG secara logika hitungan.
            // Jadi kita CUKUP update statusnya saja. Aman.

            DB::commit();
            return back()->with('success', 'Pesanan berhasil diselesaikan! Struk siap dicetak.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal memproses: ' . $e->getMessage());
        }
    }

    // Fungsi Batalkan Pesanan
    public function batalkan($id)
    {
        $transaksi = Transaksi::findOrFail($id);
        $transaksi->update(['status_pesanan' => 'batal']);
        
        // Opsional: Jika batal, kita harus menghapus detail transaksi agar stok kembali?
        // Atau biarkan status 'batal' dan kita filter di perhitungan stok.
        // Untuk simpelnya: Hapus detailnya agar stok kembali.
        $transaksi->details()->delete(); 

        return back()->with('success', 'Pesanan telah dibatalkan dan stok dikembalikan.');
    }
}