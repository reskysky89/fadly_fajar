<?php

namespace App\Http\Controllers\Admin;
use App\Notifications\PesananSelesaiNotification;
use Illuminate\Support\Facades\Mail;
use App\Mail\PesananSelesaiMail;
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
                'id_user_kasir'  => Auth::id(), 
                'nama_kasir'     => Auth::user()->nama,
                'bayar'          => $request->bayar,
                'kembalian'      => $request->bayar - $transaksi->total_harga,
                
                // --- HAPUS BARIS DI BAWAH INI ---
                // 'waktu_transaksi'=> now(), 
                // --------------------------------
                // Biarkan waktu transaksi tetap sesuai saat pelanggan checkout.
            ]);

            DB::commit();
            if ($transaksi->pelanggan && $transaksi->pelanggan->email) {
                try {
                    Mail::to($transaksi->pelanggan->email)->send(new PesananSelesaiMail($transaksi));
                } catch (\Exception $e) {
                    // Email gagal? Biarkan saja, jangan gagalkan transaksi.
                    // Cukup log error-nya jika perlu.
                }
            }

            if ($transaksi->pelanggan) {
                $transaksi->pelanggan->notify(new PesananSelesaiNotification($transaksi));
            }
            // -----------------------------------------------

            return back()->with('success', 'Pesanan selesai! Notifikasi email & website terkirim.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal memproses: ' . $e->getMessage());
        }
    }
    public function edit($id)
    {
        $transaksi = Transaksi::with(['details.produk.satuanDasar', 'details.produk.produkKonversis.satuan'])
                              ->where('id_transaksi', $id)
                              ->firstOrFail();

        // Cek status, hanya yang 'diproses' yang boleh diedit di sini
        if ($transaksi->status_pesanan != 'diproses') {
            return redirect()->route('pesanan.index')->with('error', 'Pesanan ini sudah selesai atau dibatalkan.');
        }

        return view('admin.pesanan.edit', compact('transaksi'));
    }

    /**
     * 2. Simpan Perubahan & Selesaikan Transaksi
     */
    public function update(Request $request, $id)
    {
        $transaksi = Transaksi::findOrFail($id);

        $request->validate([
            'total_harga'  => 'required|numeric',
            'bayar'        => 'required|numeric|gte:total_harga',
            'kembalian'    => 'required|numeric',
            'items'        => 'required|array|min:1', 
        ]);

        try {
            DB::beginTransaction();

            // A. Update Header Transaksi (Finalisasi)
            $transaksi->update([
                'total_harga'    => $request->total_harga,
                'bayar'          => $request->bayar,
                'kembalian'      => $request->kembalian,
                'status_pesanan' => 'selesai',       // <--- UBAH JADI SELESAI
                'id_user_kasir'  => Auth::id(),      // Catat siapa yang memproses
                'nama_kasir'     => Auth::user()->nama,
                // 'waktu_transaksi' => now(), // Opsional: Update waktu ke saat ini atau biarkan waktu order asli
            ]);

            // B. Reset Detail (Hapus Lama -> Masukkan Baru hasil Edit)
            $transaksi->details()->delete();

            foreach ($request->items as $item) {
                \App\Models\DetailTransaksi::create([
                    'id_transaksi' => $transaksi->id_transaksi,
                    'id_produk'    => $item['id_produk'],
                    'jumlah'       => $item['qty'],
                    'satuan'       => $item['satuan'],
                    // 'id_satuan' => ... (Jika sudah pakai ID)
                    'harga_satuan' => $item['harga'],
                    'subtotal'     => $item['subtotal'],
                ]);
            }

            DB::commit();

            // C. Kirim Email Notifikasi (Copy logika dari sebelumnya)
            if ($transaksi->pelanggan && $transaksi->pelanggan->email) {
                try {
                    \Illuminate\Support\Facades\Mail::to($transaksi->pelanggan->email)->send(new \App\Mail\PesananSelesaiMail($transaksi));
                } catch (\Exception $e) {}
            }
            
            // D. Kirim Notifikasi Website
            if ($transaksi->pelanggan) {
                $transaksi->pelanggan->notify(new \App\Notifications\PesananSelesaiNotification($transaksi));
            }

            return response()->json(['success' => true, 'message' => 'Pesanan berhasil diproses & diselesaikan!']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
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