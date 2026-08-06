<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Rak;
use App\Models\Lokasi;

class RakController extends Controller
{
    public function index()
    {
        $rak = Rak::with('lokasi')->get();
        return view('rak.index', compact('rak'));
    }

    public function create()
    {
        $lokasi = Lokasi::where('status', 'Aktif')->get();
        return view('rak.create', compact('lokasi'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_rak' => 'required|unique:rak,nama_rak,NULL,id_rak,id_lokasi,' . $request->id_lokasi,
            'id_lokasi' => 'required'
        ], [
            'nama_rak.required' => 'Nama rak wajib diisi.',
            'nama_rak.unique' => 'Nama rak sudah digunakan di lokasi ini.',
            'id_lokasi.required' => 'Lokasi wajib dipilih.'
        ]);

        Rak::create([
            'nama_rak' => $request->nama_rak,
            'id_lokasi' => $request->id_lokasi,
        ]);

        return redirect()->route('rak.index')->with('success', 'Data berhasil ditambahkan');
    }

    public function show(string $id_rak)
    {
        //
    }

    public function edit(string $id_rak)
    {
        $rak = Rak::findOrFail($id_rak);
        $lokasi = Lokasi::where('status', 'Aktif')
                    ->orWhere('id_lokasi', $rak->id_lokasi)
                    ->get();
        return view('rak.edit', compact('rak', 'lokasi'));
    }

    public function update(Request $request, string $id_rak)
    {
        $request->validate([
            'nama_rak'  => 'required|unique:rak,nama_rak,' . $id_rak . ',id_rak,id_lokasi,' . $request->id_lokasi,
            'id_lokasi' => 'required',
            'status'    => 'required|in:Aktif,Nonaktif',
        ], [
            'nama_rak.required'  => 'Nama rak wajib diisi.',
            'nama_rak.unique'    => 'Nama rak sudah digunakan di lokasi ini.',
            'id_lokasi.required' => 'Lokasi wajib dipilih.',
            'status.required'    => 'Status wajib dipilih.',
        ]);

        $rak = Rak::findOrFail($id_rak);
        $rak->nama_rak  = $request->nama_rak;
        $rak->id_lokasi = $request->id_lokasi;
        $rak->status    = $request->status;
        $rak->save();

        return redirect()->route('rak.index')->with('success', 'Data berhasil diupdate');
    }

    public function destroy(string $id_rak)
    {
        $rak = Rak::findOrFail($id_rak);
        $rak->delete();

        return redirect()->route('rak.index')->with('success', 'Data berhasil dihapus');
    }
}