<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View
    {
        return view('login');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }

    public function showChooseRole(): View
    {
        return view('choose-role');
    }

    public function showUserRegistration(): View
    {
        return view('register');
    }

    public function showDriverRegistration(): View
    {
        return view('register-driver');
    }

    public function login(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if ($user && Hash::check($request->password, $user->password)) {
            Auth::login($user);
            $request->session()->regenerate();

            // Redirect based on role
            if ($user->role === 'driver') {
                if (! $user->is_verified) {
                    return redirect()->route('driver.verification.pending');
                }

                return redirect()->route('driver.profile');
            }

            return redirect()->route('user.profile');
        }

        \Illuminate\Support\Facades\Log::warning('Failed login attempt', ['email' => $request->email, 'ip' => $request->ip()]);
        return back()->withErrors(['email' => 'Invalid credentials'])->withInput();
    }
}
