<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class AdminUserController extends Controller
{
    /**
     * Display a listing of users.
     */
    public function index(): View
    {
        $users = User::where('role', '!=', 'driver')
            ->orWhereNull('role')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        /** @phpstan-var view-string $view */
        $view = 'admin.users.index';
        return view($view, compact('users'));
    }

    /**
     * Show the form for creating a new user.
     */
    public function create(): View
    {
        /** @phpstan-var view-string $view */
        $view = 'admin.users.create';
        return view($view);
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'string', 'confirmed', Password::min(8)->letters()->mixedCase()->numbers()->symbols()],
            'phone' => 'nullable|string|max:20',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'role' => 'user',
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', 'User created successfully.');
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit($id): View
    {
        $user = User::findOrFail($id);

        /** @phpstan-var view-string $view */
        $view = 'admin.users.edit';
        return view($view, compact('user'));
    }

    /**
     * Update the specified user in storage.
     */
    public function update(Request $request, $id): RedirectResponse
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'password' => ['nullable', 'string', 'confirmed', Password::min(8)->letters()->mixedCase()->numbers()->symbols()],
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'role' => 'user',
        ]);

        if ($request->filled('password')) {
            $user->update(['password' => Hash::make($request->password)]);
        }

        return redirect()->route('admin.users.index')
            ->with('success', 'User updated successfully.');
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy($id): RedirectResponse
    {
        $user = User::findOrFail($id);
        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'User deleted successfully.');
    }

    /**
     * Display the specified user and related data.
     */
    public function show($id): View
    {
        $user = User::with(['ridePurchases.ride', 'rides'])->findOrFail($id);
        $today = now()->toDateString();
        $currentBookings = $user->ridePurchases->filter(function ($b) use ($today) {
            /** @var \App\Models\RidePurchase $b */
            /** @var \App\Models\Ride|null $ride */
            $ride = $b->ride;
            return $ride && $ride->date >= $today;
        });
        $pastBookings = $user->ridePurchases->filter(function ($b) use ($today) {
            /** @var \App\Models\RidePurchase $b */
            /** @var \App\Models\Ride|null $ride */
            $ride = $b->ride;
            return $ride && $ride->date < $today;
        });
        $currentRides = $user->rides->filter(function ($r) use ($today) {
            /** @var \App\Models\Ride $r */
            return $r->date >= $today;
        });
        $pastRides = $user->rides->filter(function ($r) use ($today) {
            /** @var \App\Models\Ride $r */
            return $r->date < $today;
        });

        /** @phpstan-var view-string $view */
        $view = 'admin.users.show';
        return view($view, compact('user', 'currentBookings', 'pastBookings', 'currentRides', 'pastRides'));
    }

    // API: List users
    public function apiIndex(): JsonResponse
    {
        $users = User::where('role', '!=', 'driver')
            ->orWhereNull('role')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $users,
        ]);
    }

    // API: Store user
    public function apiStore(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'string', 'confirmed', Password::min(8)->letters()->mixedCase()->numbers()->symbols()],
            'phone' => 'nullable|string|max:20',
        ]);
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => \Illuminate\Support\Facades\Hash::make($validated['password']),
            'phone' => $validated['phone'],
            'role' => 'user',
        ]);

        return response()->json([
            'success' => true,
            'data' => $user,
        ]);
    }

    // API: Show user
    public function apiShow($id): JsonResponse
    {
        $user = User::with(['ridePurchases.ride', 'rides'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $user,
        ]);
    }

    // API: Update user
    public function apiUpdate(Request $request, $id): JsonResponse
    {
        $user = User::findOrFail($id);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'password' => ['nullable', 'string', 'confirmed', Password::min(8)->letters()->mixedCase()->numbers()->symbols()],
        ]);
        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'role' => 'user',
        ]);
        if (! empty($validated['password'])) {
            $user->update(['password' => \Illuminate\Support\Facades\Hash::make($validated['password'])]);
        }

        return response()->json([
            'success' => true,
            'data' => $user,
        ]);
    }

    // API: Delete user
    public function apiDestroy($id): JsonResponse
    {
        $user = User::findOrFail($id);
        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'User deleted successfully.',
        ]);
    }
}
