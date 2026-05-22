<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class PasswordChangeController extends Controller
{
    public function show(): View
    {
        return view('change-password');
    }

    public function update(Request $request): RedirectResponse
    {
        $user = auth()->user();
        if (! $user) {
            return redirect()->route('login')->with('error', 'Please login to change your password.');
        }

        // Validation
        $request->validate([
            'current_password' => 'required',
            'new_password' => ['required', 'confirmed', Password::min(8)->letters()->mixedCase()->numbers()->symbols()],
        ]);

        // Check current password
        if (! Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.'])->withInput();
        }

        // Update password
        $user->password = Hash::make($request->new_password);
        $user->save();

        return redirect()->route('user.profile')->with('success', 'Password changed successfully!');
    }

    // API Methods
    public function apiShow(Request $request): JsonResponse
    {
        // Get user from token authentication
        $user = $request->user();
        if (! $user) {
            return response()->json([
                'message' => 'Please login to change your password.',
                'status' => 'error',
            ], 401);
        }

        return response()->json([
            'message' => 'Password change form data',
            'status' => 'success',
            'data' => [
                'form_fields' => [
                    'current_password' => 'required',
                    'new_password' => 'required|confirmed|min:8',
                ],
            ],
        ]);
    }

    public function apiUpdate(Request $request): JsonResponse
    {
        // Get user from token authentication
        $user = $request->user();
        if (! $user) {
            return response()->json([
                'message' => 'Please login to change your password.',
                'status' => 'error',
            ], 401);
        }

        // Validation
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'current_password' => 'required',
            'new_password' => ['required', 'confirmed', Password::min(8)->letters()->mixedCase()->numbers()->symbols()],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Check current password
        if (! Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'message' => 'Current password is incorrect.',
                'status' => 'error',
                'errors' => ['current_password' => ['Current password is incorrect.']],
            ], 422);
        }

        // Update password
        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json([
            'message' => 'Password changed successfully!',
            'status' => 'success',
        ]);
    }
}
