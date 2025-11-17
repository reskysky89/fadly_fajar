<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Supplier; // <-- Import Model
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    /**
     * Menyimpan supplier baru ke database.
     * Didesain untuk menangani permintaan AJAX dari modal.
     */
    public function store(Request $request)
    {
        // 1. Validasi
        $validated = $request->validate([
            'nama_supplier' => 'required|string|max:150|unique:supplier,nama_supplier',
            'kontak' => 'nullable|string',
            'keterangan' => 'nullable|string',
        ]);

        // 2. Simpan ke database
        // Pastikan Model 'Supplier' Anda punya $fillable
        $supplier = Supplier::create($validated); 

        // 3. Cek apakah ini permintaan AJAX (dari modal)
        if ($request->wantsJson()) {
            // Jika ya, kembalikan data supplier yang baru dibuat
            return response()->json([
                'success' => true,
                'message' => 'Supplier baru berhasil ditambahkan.',
                'supplier' => $supplier // Kirim data supplier baru kembali
            ], 201); // 201 = Status "Created"
        }

        // 4. Fallback jika ada form biasa
        return redirect()->route('admin.dashboard')
                         ->with('success', 'Supplier baru berhasil ditambahkan.');
    }
}