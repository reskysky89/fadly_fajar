<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kategori; // <-- Import Model
use Illuminate\Http\Request;

class KategoriController extends Controller
{
    /**
     * Menyimpan kategori baru ke database.
     * Didesain untuk menangani permintaan AJAX dari modal.
     */
    public function store(Request $request)
    {
        // 1. Validasi
        $validated = $request->validate([
            'nama_kategori' => 'required|string|max:100|unique:kategori,nama_kategori',
            'deskripsi' => 'nullable|string',
        ]);

        // 2. Simpan ke database
        // Pastikan Model 'Kategori' Anda punya $fillable
        $kategori = Kategori::create($validated); 

        // 3. LOGIKA KUNCI: Cek apakah ini permintaan AJAX (dari modal)
        if ($request->wantsJson()) {
            // Jika ya, kembalikan data kategori yang baru dibuat
            return response()->json([
                'success' => true,
                'message' => 'Kategori baru berhasil ditambahkan.',
                'kategori' => $kategori // Kirim data kategori baru kembali
            ], 201); // 201 = Status "Created"
        }

        // 4. Fallback jika ada form biasa (tidak akan terpakai di skenario ini)
        return redirect()->route('admin.dashboard')
                         ->with('success', 'Kategori baru berhasil ditambahkan.');
    }
}