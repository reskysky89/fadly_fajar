<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
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
        $request->user()->fill($request->validated());

        // Pastikan field alamat ikut tersimpan jika ada di request
        if ($request->has('alamat')) {
            $request->user()->alamat = $request->alamat;
        }

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

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
