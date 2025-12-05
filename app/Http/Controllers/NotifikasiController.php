<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotifikasiController extends Controller
{
    // Baca 1 Notifikasi lalu Redirect
    public function baca($id)
    {
        $notif = Auth::user()->notifications()->where('id', $id)->first();
        if ($notif) {
            $notif->markAsRead();
            return redirect($notif->data['link']); // Ke halaman riwayat
        }
        return back();
    }

    // Baca Semua
    public function bacaSemua()
    {
        Auth::user()->unreadNotifications->markAsRead();
        return back();
    }
}