<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:'.User::class],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'kontak' => ['required', 'string', 'max:20'], // Tambahan
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'nama' => $request->nama,
            'username' => $request->username,
            'email' => $request->email,
            'kontak' => $request->kontak, // Simpan No HP
            'password' => Hash::make($request->password),
            'role_user' => 'pelanggan',   // OTOMATIS JADI PELANGGAN
            'status_akun' => 'aktif',
        ]);

        event(new Registered($user)); // INI YANG MEMICU KIRIM EMAIL

        Auth::login($user);

        // Redirect ke halaman verifikasi email (bawaan Laravel)
        return redirect()->route('verification.notice');
    }
}
