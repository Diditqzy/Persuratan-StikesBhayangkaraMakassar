<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $user = $request->user();

        // 1. Jika User Biasa -> Masuk ke Halaman "Surat Saya"
        if ($user->role === 'user') {
            return redirect()->route('user.letters.index');
        }

        // 2. Jika Admin atau Pimpinan -> Masuk ke Filament Panel
        // Filament secara default berada di path '/admin'
        if (in_array($user->role, ['admin', 'pimpinan'])) {
            return redirect()->to('/admin'); 
        }

        // 3. Fallback (Jaga-jaga jika ada role lain di masa depan)
        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
