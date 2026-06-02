<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, $role): Response
    {
        // Cek apakah user belum login, atau role-nya (Enum) tidak sama dengan yang diminta rute
        if (!Auth::check() || Auth::user()->role->value !== $role) {
            // Jika melanggar, tendang kembali ke halaman login
            return redirect('/login')->withErrors([
                'email' => 'Akses ditolak: Jabatan Anda tidak sesuai untuk halaman ini.'
            ]);
        }

        return $next($request);
    }
}