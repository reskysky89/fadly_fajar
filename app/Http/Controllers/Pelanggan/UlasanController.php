<?php

namespace App\Http\Controllers\Pelanggan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Ulasan;
use App\Models\Transaksi;
use Illuminate\Support\Facades\Auth;

class UlasanController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'id_transaksi' => 'required|exists:transaksi,id_transaksi',
            'rating'       => 'required|integer|min:1|max:5',
            'komentar'     => 'nullable|string|max:500', // Boleh kosong (Tidak Wajib)
        ]);

        // 1. Cek Validitas: Transaksi milik user & status selesai
        $transaksi = Transaksi::where('id_transaksi', $request->id_transaksi)
                              ->where('id_user_pelanggan', Auth::id())
                              ->where('status_pesanan', 'selesai')
                              ->first();

        if (!$transaksi) {
            return back()->with('error', 'Transaksi tidak valid atau belum selesai.');
        }

        // 2. Cek Double: Apakah transaksi ini sudah pernah diulas?
        $sudahUlas = Ulasan::where('id_transaksi', $request->id_transaksi)->exists();

        if ($sudahUlas) {
            return back()->with('error', 'Anda sudah memberikan ulasan untuk pesanan ini.');
        }

        // 3. Simpan Ulasan Transaksi
        Ulasan::create([
            'id_user'      => Auth::id(),
            'id_transaksi' => $request->id_transaksi,
            'rating'       => $request->rating,
            'komentar'     => $request->komentar
        ]);

        return back()->with('success', 'Terima kasih! Ulasan transaksi Anda berhasil dikirim.');
    }
}