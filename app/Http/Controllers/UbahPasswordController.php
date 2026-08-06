<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UbahPasswordController extends Controller
{
    public function index()
    {
        return view('ubah-password');
    }

    public function update(Request $request)
    {
        $request->validate([
            'password_lama' => 'required',
            'password_baru' => 'required|min:8|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/',
            'konfirmasi_password' => 'required|same:password_baru',
        ], [
            'password_lama.required' => 'Password lama wajib diisi.',
            'password_baru.required' => 'Password baru wajib diisi.',
            'password_baru.min' => 'Password baru minimal 8 karakter.',
            'password_baru.regex' => 'Password baru harus mengandung huruf besar, huruf kecil, dan angka.',
            'konfirmasi_password.required' => 'Konfirmasi password wajib diisi.',
            'konfirmasi_password.same' => 'Konfirmasi password tidak cocok denganpassword baru.',
        ]);

        // Cek password lama
        if (!Hash::check($request->password_lama, Auth::user()->password)) {
            return back()->withErrors([
                'password_lama' => 'Password lama tidak sesuai.'
            ]);
        }

        // Update password
        Auth::user()->update([
            'password' => Hash::make($request->password_baru)
        ]);

        return redirect()->route('ubah-password')->with('success', 'Password berhasil diubah!');
    }
}