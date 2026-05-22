<?php

namespace App\Http\Controllers;

use App\Models\User;
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

        return view('admin.users.index', compact('users'));
    }

    /**
     * Show the form for creating a new user.
     */
    public function create(): View
    {
        return view('admin.users.create');
    }

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

        return view('admin.users.edit', compact('user'));
    }

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

        return view('admin.users.show', compact('user', 'currentBookings', 'pastBookings', 'currentRides', 'pastRides'));
    }
}
