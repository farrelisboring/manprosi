<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function index()
    {
        return view('auth.login');
    }

    public function authenticate(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
            'role' => ['required']
        ]);

        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            
            // Ambil role dari database (antisipasi jika format objek/string berbeda)
            $userRole = Auth::user()->role;
            $dbRole = $userRole instanceof \BackedEnum ? $userRole->value : $userRole;
            
            // Jadikan huruf kecil semua agar pengecekannya 100% akurat
            if (strtolower($dbRole) !== strtolower($request->role)) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                
                // Kembalikan ke halaman login dengan pesan error
                return back()->withErrors([
                    'email' => 'Akses ditolak: Anda memilih Role yang salah untuk akun ini.'
                ])->onlyInput('email');
            }

            $request->session()->regenerate();
            return redirect()->route('dashboard'); 
        }

        return back()->withErrors(['email' => 'Email atau password salah.'])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }
}