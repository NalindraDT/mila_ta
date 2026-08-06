<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Lokasi;

class LokasiController extends Controller
{
    public function index()
    {
        $lokasi = Lokasi::all();
        return view('lokasi.index', compact('lokasi'));
    }

    public function create()
    {
        return view('lokasi.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_lokasi' => 'required|unique:lokasi,nama_lokasi'
        ], [
            'nama_lokasi.required' => 'Nama lokasi wajib diisi.',
            'nama_lokasi.unique' => 'Nama lokasi sudah digunakan.'
        ]);

        Lokasi::create([
            'nama_lokasi' => $request->nama_lokasi
        ]);

        return redirect()->route('lokasi.index')->with('success', 'Data berhasil ditambahkan');
    }

    public function show(string $id_lokasi)
    {
        //
    }

    public function edit(string $id_lokasi)
    {
        $lokasi = Lokasi::findOrFail($id_lokasi);

        return view('lokasi.edit', compact('lokasi'));
    }

    public function update(Request $request, string $id_lokasi)
    {
        $request->validate([
            'nama_lokasi' => 'required|unique:lokasi,nama_lokasi,' . $id_lokasi . ',id_lokasi',
            'status'      => 'required|in:Aktif,Nonaktif',
        ], [
            'nama_lokasi.required' => 'Nama lokasi wajib diisi.',
            'nama_lokasi.unique'   => 'Nama lokasi sudah digunakan.',
            'status.required'      => 'Status wajib dipilih.',
        ]);

        $lokasi = Lokasi::findOrFail($id_lokasi);
        $lokasi->nama_lokasi = $request->nama_lokasi;
        $lokasi->status      = $request->status;
        $lokasi->save();

        return redirect()->route('lokasi.index')->with('success', 'Data berhasil diupdate');
    }

    public function destroy(string $id_lokasi)
    {
        $data = Lokasi::findOrFail($id_lokasi);
        $data->delete();

        return redirect()->route('lokasi.index')->with('success', 'Data berhasil dihapus');
    }
}