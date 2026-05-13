<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('login');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('home');
    }

    public function showChooseRole()
    {
        return view('choose-role');
    }

    public function showUserRegistration()
    {
        return view('register');
    }

    public function showDriverRegistration()
    {
        return view('register-driver');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();
        
        if ($user && Hash::check($request->password, $user->password)) {
            Auth::login($user);
            
            // Redirect based on role
            if ($user->role === 'driver') {
                if (!$user->is_verified) {
                    return redirect()->route('driver.verification.pending');
                }
                return redirect()->route('driver.profile');
            }
            
            return redirect()->route('user.profile');
        }
        
        return back()->withErrors(['email' => 'Invalid credentials'])->withInput();
    }
} 