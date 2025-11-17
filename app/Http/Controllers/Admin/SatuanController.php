<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Satuan;
use Illuminate\Http\Request;

class SatuanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $satuans = Satuan::latest()->get(); // Ambil semua data satuan
        return view('admin.satuan.index', compact('satuans'));
    
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama_satuan' => 'required|string|max:50|unique:satuan,nama_satuan',
            'keterangan' => 'nullable|string',
        ]);

        // Simpan ke database
        $satuan = new Satuan();
        $satuan->nama_satuan = $request->nama_satuan;
        $satuan->keterangan = $request->keterangan;
        $satuan->save();

        return redirect()->route('admin.satuan.index')
                         ->with('success', 'Satuan baru berhasil ditambahkan.');
    
    }

    /**
     * Display the specified resource.
     */
    public function show(Satuan $satuan)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Satuan $satuan)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Satuan $satuan)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Satuan $satuan)
    {
        //
    }
}
