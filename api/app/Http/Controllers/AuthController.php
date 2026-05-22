<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
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
                return redirect()->route('driver.profile');
            } else {
                return redirect()->route('user.profile');
            }
        }

        // If we get here, login failed
        \Illuminate\Support\Facades\Log::warning('Failed login attempt', ['email' => $request->email, 'ip' => $request->ip()]);
        return back()->withErrors(['email' => 'Invalid credentials'])->withInput();
    }

    // API Methods
    public function apiShowLogin(): JsonResponse
    {
        return response()->json([
            'message' => 'Login form data',
            'status' => 'success',
            'data' => [
                'form_fields' => [
                    'email' => 'required|email',
                    'password' => 'required',
                ],
            ],
        ]);
    }

    public function apiLogout(Request $request): JsonResponse
    {
        $request->session()->forget(['user', 'user_role']);

        return response()->json([
            'message' => 'Logged out successfully',
            'status' => 'success',
        ]);
    }

    public function apiShowChooseRole(): JsonResponse
    {
        return response()->json([
            'message' => 'Choose registration role',
            'status' => 'success',
            'data' => [
                'roles' => ['user', 'driver'],
                'description' => 'Select whether you want to register as a user or driver',
            ],
        ]);
    }

    public function apiShowUserRegistration(): JsonResponse
    {
        return response()->json([
            'message' => 'User registration form data',
            'status' => 'success',
            'data' => [
                'form_fields' => [
                    'name' => 'required|string|max:255',
                    'email' => 'required|email|unique:users',
                    'password' => 'required|string|min:8|confirmed',
                    'phone' => 'required|string|max:20',
                    'date_of_birth' => 'nullable|date|before:today',
                    'address' => 'nullable|string|max:500',
                ],
            ],
        ]);
    }

    public function apiShowDriverRegistration(): JsonResponse
    {
        return response()->json([
            'message' => 'Driver registration form data',
            'status' => 'success',
            'data' => [
                'form_fields' => [
                    'name' => 'required|string|max:255',
                    'email' => 'required|email|unique:users',
                    'password' => 'required|string|min:8|confirmed',
                    'phone' => 'required|string|max:20',
                    'license_number' => 'required|string|max:255',
                    'license_expiry' => 'required|date|after:today',
                    'vehicle_model' => 'required|string|max:255',
                    'vehicle_year' => 'required|integer|min:1900|max:' . (date('Y') + 1),
                    'vehicle_color' => 'required|string|max:255',
                    'license_plate' => 'required|string|max:255',
                    'vehicle_seats' => 'required|integer|min:1|max:20',
                ],
            ],
        ]);
    }

    public function apiLogin(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = \App\Models\User::where('email', $request->email)->first();

        if ($user && \Illuminate\Support\Facades\Hash::check($request->password, $user->password)) {
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

            return response()->json([
                'message' => 'Login successful',
                'status' => 'success',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'role' => $role,
                    ],
                    'redirect_url' => $role === 'driver' ? '/driver/profile' : '/profile',
                ],
            ]);
        }

        \Illuminate\Support\Facades\Log::warning('Failed API login attempt', ['email' => $request->email, 'ip' => $request->ip()]);
        return response()->json([
            'message' => 'Invalid credentials',
            'status' => 'error',
        ], 401);
    }
}
