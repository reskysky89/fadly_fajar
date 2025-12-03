<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;

class VerifyEmailController extends Controller
{
    /**
     * Mark the authenticated user's email address as verified.
     */
    public function __invoke(EmailVerificationRequest $request): RedirectResponse
    {
        // 1. Cek apakah email sudah diverifikasi sebelumnya?
        if ($request->user()->hasVerifiedEmail()) {
            return $this->redirectBasedOnRole($request->user());
        }

        // 2. Jika belum, verifikasi sekarang!
        if ($request->user()->markEmailAsVerified()) {
            event(new Verified($request->user()));
        }

        // 3. Arahkan sesuai Role
        return $this->redirectBasedOnRole($request->user())->with('verified', 1);
    }

    /**
     * Fungsi Tambahan: Menentukan Arah Redirect
     */
    protected function redirectBasedOnRole($user)
    {
        $role = $user->role_user;

        if ($role === 'admin') {
            return redirect()->route('admin.dashboard');
        } elseif ($role === 'kasir') {
            return redirect()->route('kasir.transaksi.index');
        } else {
            // PELANGGAN: Arahkan ke Halaman Utama (Home) untuk belanja
            return redirect()->route('home');
        }
    }
}
