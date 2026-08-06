<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class ManajemenUserController extends Controller
{
    public function index()
    {
        $users = User::all();
        return view('manajemen-user.index', compact('users'));
    }

    public function create()
    {
        return view('manajemen-user.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_user' => 'required',
            'username'  => 'required|min:5|max:20|unique:user,username|regex:/^[a-zA-Z0-9_]+$/',
            'password'  => 'required|min:8|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/',
            'role'      => 'required',
        ], [
            'nama_user.required' => 'Nama user wajib diisi.',
            'username.required'  => 'Username wajib diisi.',
            'username.min'       => 'Username minimal 5 karakter.',
            'username.max'       => 'Username maksimal 20 karakter.',
            'username.unique'    => 'Username sudah digunakan.',
            'username.regex'     => 'Username hanya boleh huruf, angka, dan underscore.',
            'password.required'  => 'Password wajib diisi.',
            'password.min'       => 'Password minimal 8 karakter.',
            'password.regex'     => 'Password harus mengandung huruf besar, huruf kecil, dan angka.',
            'role.required'      => 'Role wajib dipilih.',
        ]);

        User::create([
            'nama_user' => $request->nama_user,
            'username'  => $request->username,
            'password'  => Hash::make($request->password),
            'role'      => $request->role,
        ]);

        return redirect()->route('manajemen-user.index')->with('success', 'User berhasil ditambahkan');
    }

    public function edit(string $id_user)
    {
        $user = User::findOrFail($id_user);
        return view('manajemen-user.edit', compact('user'));
    }

    public function update(Request $request, string $id_user)
    {
        $user = User::findOrFail($id_user);

        $request->validate([
            'nama_user' => 'required',
            'username'  => 'required|min:5|max:20|unique:user,username,' . $id_user . ',id_user|regex:/^[a-zA-Z0-9_]+$/',
            'role'      => 'required',
        ], [
            'nama_user.required' => 'Nama user wajib diisi.',
            'username.required'  => 'Username wajib diisi.',
            'username.min'       => 'Username minimal 5 karakter.',
            'username.max'       => 'Username maksimal 20 karakter.',
            'username.unique'    => 'Username sudah digunakan.',
            'username.regex'     => 'Username hanya boleh huruf, angka, dan underscore.',
            'role.required'      => 'Role wajib dipilih.',
        ]);

        $user->nama_user = $request->nama_user;
        $user->username  = $request->username;
        $user->role      = $request->role;

        if ($request->filled('password')) {
            $request->validate([
                'password' => 'min:8|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/',
            ], [
                'password.min'   => 'Password minimal 8 karakter.',
                'password.regex' => 'Password harus mengandung huruf besar, huruf kecil, dan angka.',
            ]);
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return redirect()->route('manajemen-user.index')->with('success', 'User berhasil diupdate');
    }

    public function destroy(string $id_user)
    {
        $user = User::findOrFail($id_user);
        $user->delete();

        return redirect()->route('manajemen-user.index')->with('success', 'User berhasil dihapus');
    }
}