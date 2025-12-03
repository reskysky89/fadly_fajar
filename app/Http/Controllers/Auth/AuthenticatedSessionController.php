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
        // 1. Cek Username & Password
        $request->authenticate();

        // 2. Regenerasi Session (Standar Keamanan)
        $request->session()->regenerate();

        $user = $request->user();

        // --- LOGIKA BARU: CEK VERIFIKASI ---
        // Khusus Pelanggan: Jika belum verifikasi email, langsung lempar ke halaman verifikasi
        if ($user->role_user === 'pelanggan' && ! $user->hasVerifiedEmail()) {
            return redirect()->route('verification.notice');
        }
        // -----------------------------------

        // 3. Jika Sudah Verifikasi (atau Admin/Kasir), Arahkan sesuai Role
        if ($user->role_user === 'admin') {
            return redirect()->route('admin.dashboard');
        } elseif ($user->role_user === 'kasir') {
            return redirect()->route('kasir.transaksi.index');
        } else {
            // Pelanggan Verified -> Ke Home
            return redirect()->intended(route('home'));
        }
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