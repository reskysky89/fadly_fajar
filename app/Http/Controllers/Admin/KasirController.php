<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules;

class KasirController extends Controller
{
    /**
     * Menampilkan daftar kasir
     */
    public function index(Request $request)
    {
        $query = User::where('role_user', 'kasir');

        // Fitur Pencarian
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nama', 'like', '%' . $search . '%')
                  ->orWhere('email', 'like', '%' . $search . '%')
                  ->orWhere('kontak', 'like', '%' . $search . '%');
            });
        }

        $kasirs = $query->latest()->paginate(10);
        
        return view('admin.kasir.index', compact('kasirs'));
    }

    /**
     * Form tambah kasir
     */
    public function create()
    {
        return view('admin.kasir.create');
    }

    /**
     * Simpan kasir baru
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:users'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users'],
            'kontak' => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()], // Password manual wajib
            'foto_profil' => ['nullable', 'image', 'max:2048'],
        ]);

        // Handle Foto (Opsional)
        $fotoPath = null;
        if ($request->hasFile('foto_profil')) {
            $fotoPath = $request->file('foto_profil')->store('profil', 'public');
        }

        User::create([
            'nama' => $request->nama,
            'username' => $request->username,
            'email' => $request->email,
            'kontak' => $request->kontak,
            'password' => Hash::make($request->password),
            'role_user' => 'kasir', // Otomatis set sebagai kasir
            'status_akun' => 'aktif', // Default aktif
            'foto_profil' => $fotoPath,
        ]);

        return redirect()->route('admin.kasir.index')
                         ->with('success', 'Akun kasir berhasil dibuat.');
    }

    /**
     * Form edit kasir
     */
    public function edit($id)
    {
        // Cari user berdasarkan ID dan pastikan dia adalah kasir (bukan admin/pelanggan)
        $kasir = User::where('id_user', $id)->where('role_user', 'kasir')->firstOrFail();
        
        return view('admin.kasir.edit', compact('kasir'));
    }

    /**
     * Update data kasir
     */
    public function update(Request $request, $id)
    {
        $kasir = User::where('id_user', $id)->where('role_user', 'kasir')->firstOrFail();

        $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            // Ignore unique check untuk email/username milik user ini sendiri
            'username' => ['required', 'string', 'max:255', 'unique:users,username,'.$kasir->id_user.',id_user'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,'.$kasir->id_user.',id_user'],
            'kontak' => ['nullable', 'string', 'max:20'],
            'foto_profil' => ['nullable', 'image', 'max:2048'],
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()], // Password opsional saat edit
        ]);

        // Handle Foto Baru
        if ($request->hasFile('foto_profil')) {
            if ($kasir->foto_profil) {
                Storage::disk('public')->delete($kasir->foto_profil);
            }
            $kasir->foto_profil = $request->file('foto_profil')->store('profil', 'public');
        }

        $kasir->nama = $request->nama;
        $kasir->username = $request->username;
        $kasir->email = $request->email;
        $kasir->kontak = $request->kontak;

        // Hanya update password jika diisi
        if ($request->filled('password')) {
            $kasir->password = Hash::make($request->password);
        }

        $kasir->save();

        return redirect()->route('admin.kasir.index')
                         ->with('success', 'Data kasir berhasil diperbarui.');
    }

    /**
     * Toggle Status (Aktif/Nonaktif) pengganti Delete
     */
    public function toggleStatus($id)
    {
        $kasir = User::where('id_user', $id)->where('role_user', 'kasir')->firstOrFail();

        // Balik statusnya
        $kasir->status_akun = ($kasir->status_akun == 'aktif') ? 'nonaktif' : 'aktif';
        $kasir->save();

        $statusMsg = $kasir->status_akun == 'aktif' ? 'diaktifkan' : 'dinonaktifkan';

        return redirect()->route('admin.kasir.index')
                         ->with('success', "Akun kasir berhasil $statusMsg.");
    }
}