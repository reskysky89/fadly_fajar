<?php

namespace App\Http\Controllers\Pelanggan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Transaksi;
use Illuminate\Support\Facades\Auth;

class RiwayatPesananController extends Controller
{
    public function index()
    {
        // Ambil transaksi milik user yang login
        $riwayat = Transaksi::with(['details.produk'])
                            ->where('id_user_pelanggan', Auth::id())
                            ->where('jenis_transaksi', 'online') // Hanya pesanan online
                            ->latest('waktu_transaksi') // Urutkan dari yang terbaru
                            ->paginate(10); // Pakai pagination biar rapi

        return view('pelanggan.riwayat.index', compact('riwayat'));
    }
}