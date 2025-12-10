<?php

namespace App\Http\Controllers\Admin;
use App\Notifications\PesananSelesaiNotification;
use Illuminate\Support\Facades\Mail;
use App\Mail\PesananSelesaiMail;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Transaksi;
use App\Models\DetailTransaksi;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PesananOnlineController extends Controller
{
    public function index()
    {
        // 1. PESANAN BARU (DIPROSES) - Tetap Card
        $pesananBaru = Transaksi::with(['pelanggan', 'details.produk'])
                                ->where('jenis_transaksi', 'online')
                                ->where('status_pesanan', 'diproses')
                                ->orderBy('id_transaksi', 'desc')
                                ->get();

        // 2. RIWAYAT SELESAI (SELESAI / BATAL)
        // Ubah paginate() jadi get() agar jadi satu list panjang
        $riwayatSelesai = Transaksi::with(['pelanggan', 'kasir'])
                                   ->where('jenis_transaksi', 'online')
                                   ->whereIn('status_pesanan', ['selesai', 'batal'])
                                   ->orderBy('id_transaksi', 'desc') 
                                   ->get(); // <--- GANTI JADI GET()

        return view('admin.pesanan.index', compact('pesananBaru', 'riwayatSelesai'));
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

            // UPDATE HEADER
            $transaksi->update([
                'total_harga'    => $request->total_harga,
                'bayar'          => $request->bayar,
                'kembalian'      => $request->kembalian,
                'status_pesanan' => 'selesai',
                'id_user_kasir'  => Auth::id(),
                'nama_kasir'     => Auth::user()->nama,
                
                // --- PERBAIKAN: UPDATE WAKTU KE SEKARANG (SAAT STRUK KELUAR) ---
                // Ini agar transaksi tercatat di laporan hari ini, bukan hari pemesanan
                'waktu_transaksi'   => now(), 
                'tanggal_transaksi' => date('Y-m-d'), // Update tanggalnya juga
                // ---------------------------------------------------------------
            ]);

            // Reset Detail
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

            // Notifikasi
            if ($transaksi->pelanggan) {
                if ($transaksi->pelanggan->email) {
                    try {
                        \Illuminate\Support\Facades\Mail::to($transaksi->pelanggan->email)->send(new \App\Mail\PesananSelesaiMail($transaksi));
                    } catch (\Exception $e) {}
                }
                $transaksi->pelanggan->notify(new \App\Notifications\PesananSelesaiNotification($transaksi));
            }

            return response()->json(['success' => true, 'message' => 'Pesanan selesai! Tanggal transaksi diperbarui.']);

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
    public function cetakPickingList($id)
    {
        $transaksi = Transaksi::with(['pelanggan', 'details.produk'])
                              ->where('id_transaksi', $id)
                              ->firstOrFail();

        return view('admin.pesanan.picking_list', compact('transaksi'));
    }
}