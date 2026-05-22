<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View
    {
        return view('login');
    }

    public function logout(Request $request): RedirectResponse
    {
        \Illuminate\Support\Facades\Auth::logout();
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

        $user = \App\Models\User::where('email', $request->email)->first();

        if ($user && \Illuminate\Support\Facades\Hash::check($request->password, $user->password)) {
            $request->session()->regenerate();

            // Split name for session
            $nameParts = explode(' ', $user->name, 2);
            $firstName = $nameParts[0];
            $lastName = $nameParts[1] ?? '';
            $role = $user->role ?? 'user';

            $request->session()->put('user', [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'first_name' => $firstName,
                'last_name' => $lastName,
            ]);
            $request->session()->put('user_role', $role);

            // Redirect based on role
            if ($role === 'driver') {
                // Check if driver is verified
                if (! $user->is_verified) {
                    return redirect()->route('driver.verification.pending');
                }

                return redirect()->route('driver.profile');
            } else {
                return redirect()->route('user.profile');
            }
        }

        // If we get here, login failed
        \Illuminate\Support\Facades\Log::warning('Failed login attempt', ['email' => $request->email, 'ip' => $request->ip()]);
        return back()->withErrors(['email' => 'Invalid credentials'])->withInput();
    }
}
