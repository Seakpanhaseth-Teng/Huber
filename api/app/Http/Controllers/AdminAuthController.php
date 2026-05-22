<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AdminAuthController extends Controller
{
    public function showLogin(): View
    {
        return view('admin-login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::guard('admin')->attempt($credentials)) {
            $request->session()->regenerate();

            return redirect()->route('admin.dashboard');
        }

        \Illuminate\Support\Facades\Log::warning('Failed admin login attempt', ['email' => $credentials['email'], 'ip' => $request->ip()]);
        return back()->withErrors(['email' => 'Invalid credentials'])->withInput();
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }

    // API: Admin Login
    public function apiLogin(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $admin = \App\Models\Admin::where('email', $credentials['email'])->first();
        if ($admin && \Illuminate\Support\Facades\Hash::check($credentials['password'], $admin->password)) {
            // Create token for API access
            $token = $admin->createToken('admin_auth_token')->plainTextToken;

            return response()->json([
                'success' => true,
                'token' => $token,
                'admin' => [
                    'id' => $admin->id,
                    'name' => $admin->name,
                    'email' => $admin->email,
                ],
            ]);
        }

        \Illuminate\Support\Facades\Log::warning('Failed admin API login attempt', ['email' => $credentials['email'], 'ip' => $request->ip()]);
        return response()->json(['success' => false, 'message' => 'Invalid credentials.'], 401);
    }

    // API: Admin Logout
    public function apiLogout(Request $request): JsonResponse
    {
        if ($request->user()) {
            $request->user()->currentAccessToken()->delete();
        }

        return response()->json(['success' => true, 'message' => 'Logged out successfully.']);
    }
}
