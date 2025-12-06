<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        // Hitung Statistik User
        $user = $request->user();
        
        $totalTransaksi = \App\Models\Transaksi::where('id_user_pelanggan', $user->id_user)
                            ->where('status_pesanan', 'selesai')
                            ->count();

        $totalBelanja = \App\Models\Transaksi::where('id_user_pelanggan', $user->id_user)
                            ->where('status_pesanan', 'selesai')
                            ->sum('total_harga');

        // Kita arahkan ke view baru yang khusus Pelanggan
        return view('pelanggan.profil.index', [
            'user' => $user,
            'totalTransaksi' => $totalTransaksi,
            'totalBelanja' => $totalBelanja
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $user->fill($request->validated());

        // --- LOGIKA UPLOAD FOTO PROFIL ---
        if ($request->hasFile('foto_profil')) {
            $file = $request->file('foto_profil');
            
            // 1. Hapus Foto Lama (Jika ada dan bukan placeholder)
            if ($user->foto_profil && file_exists(public_path($user->foto_profil))) {
                unlink(public_path($user->foto_profil));
            }

            // 2. Buat Nama Unik
            $filename = time() . '_' . Str::slug($user->username) . '.' . $file->getClientOriginalExtension();

            // 3. Simpan ke Public (Jalan Tol)
            $file->move(public_path('uploads/profil'), $filename);

            // 4. Simpan Path ke Database
            $user->foto_profil = 'uploads/profil/' . $filename;
        }
        // ---------------------------------

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }
    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
