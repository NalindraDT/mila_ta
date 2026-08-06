<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Satuan;
use App\Models\Barang;

class SatuanController extends Controller
{
    public function index()
    {
        $satuan = Satuan::all()->map(function ($item) {
            $item->sedang_dipakai = Barang::where('id_satuan', $item->id_satuan)->exists();
            return $item;
        });

        return view('satuan.index', compact('satuan'));
    }

    public function create()
    {
        return view('satuan.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_satuan' => 'required|unique:satuan,nama_satuan'
        ], [
            'nama_satuan.required' => 'Nama satuan wajib diisi.',
            'nama_satuan.unique' => 'Nama satuan sudah digunakan.'
        ]);

        Satuan::create([
            'nama_satuan' => $request->nama_satuan
        ]);

        return redirect('/satuan');
    }

    public function edit($id_satuan)
    {
        $satuan = Satuan::findOrFail($id_satuan);
        return view('satuan.edit', compact('satuan'));
    }

    public function update(Request $request, $id_satuan)
    {
        $request->validate([
            'nama_satuan' => 'required|unique:satuan,nama_satuan,' . $id_satuan . ',id_satuan'
        ], [
            'nama_satuan.required' => 'Nama satuan wajib diisi.',
            'nama_satuan.unique' => 'Nama satuan sudah digunakan.'
        ]);

        $satuan = Satuan::findOrFail($id_satuan);
        $satuan->update([
            'nama_satuan' => $request->nama_satuan
        ]);

        return redirect()->route('satuan.index');
    }

    public function destroy($id_satuan)
    {
        $data = Satuan::findOrFail($id_satuan);

        if (Barang::where('id_satuan', $id_satuan)->exists()) {
            return redirect()->route('satuan.index')
                ->with('error', 'Satuan tidak bisa dihapus karena masih dipakai oleh data barang.');
        }

        $data->delete();

        return redirect()->route('satuan.index')->with('success', 'Data berhasil dihapus');
    }
}