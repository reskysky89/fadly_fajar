<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use App\Providers\RouteServiceProvider; // <-- Saya tambahkan ini untuk redirect default

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login'); // <-- Biarkan apa adanya
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate(); // <-- Ini menjalankan logika dari LoginRequest.php (Langkah 3)

        $request->session()->regenerate(); // <-- Ini membuat sesi baru

        // --- MULAI MODIFIKASI MULTI-PERAN ---

        // 1. Ambil data user yang baru saja login
        $user = Auth::user(); // <-- TAMBAHAN

        // 2. Cek apakah akunnya aktif (sesuai ERD)
        if ($user->status_akun !== 'aktif') { // <-- TAMBAHAN
            Auth::guard('web')->logout(); // <-- TAMBAHAN (Logout paksa jika tidak aktif)
            $request->session()->invalidate(); // <-- TAMBAHAN
            $request->session()->regenerateToken(); // <-- TAMBAHAN
            
            // Kirim pesan error kembali ke halaman login
            return redirect('/login')->withErrors(['login' => 'Akun Anda telah dinonaktifkan. Hubungi Admin.']); // <-- TAMBAHAN
        }
        
        // 3. Update 'last_login' (sesuai ERD)
        $user->last_login = now(); // <-- TAMBAHAN
        $user->save(); // <-- TAMBAHAN (Simpan perubahan ke database)

        // 4. Arahkan berdasarkan 'role_user' (sesuai ERD)
        if ($user->role_user === 'admin') { // <-- TAMBAHAN
            // Jika admin, arahkan ke rute '/admin/dashboard'
            return redirect()->intended('/admin/dashboard'); // <-- TAMBAHAN
        } 
        
        if ($user->role_user === 'kasir') {
            // Langsung ke halaman penjualan
            return redirect()->intended(route('kasir.transaksi.index')); 
        }
        // 5. Default untuk 'pelanggan' (Menggantikan baris asli)
        return redirect()->intended(RouteServiceProvider::HOME); // <-- PENGGANTI (HOME = '/dashboard')
        
        // --- AKHIR MODIFIKASI ---
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout(); // <-- Biarkan apa adanya

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}