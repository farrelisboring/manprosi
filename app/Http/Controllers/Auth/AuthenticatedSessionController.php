<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthenticatedSessionController extends Controller
{
    public function create(): \Illuminate\View\View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $credentials = $this->resolveCredentials($request->validated('login_identity'), $request->validated('password'));

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'login_identity' => 'Email/username atau password tidak cocok.',
            ]);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    /**
     * @return array<string, string>
     */
    protected function resolveCredentials(string $identity, string $password): array
    {
        $user = User::query()
            ->whereRaw('LOWER(email) = ?', [mb_strtolower($identity)])
            ->orWhereRaw('LOWER(name) = ?', [mb_strtolower($identity)])
            ->first();

        return [
            'email' => $user?->email ?? $identity,
            'password' => $password,
        ];
    }
}
