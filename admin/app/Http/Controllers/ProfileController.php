<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function show(): View
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        // Split name into first and last name
        $nameParts = explode(' ', $user->name, 2);
        $firstName = $nameParts[0];
        $lastName = $nameParts[1] ?? '';

        return view('profile', compact('user', 'firstName', 'lastName'));
    }

    public function update(Request $request): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        // Validation rules
        $rules = [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'required|string|max:20',
            'date_of_birth' => 'nullable|date|before:today',
            'address' => 'nullable|string|max:500',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:20',
            'emergency_contact_relationship' => 'nullable|string|max:100',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'license_number' => 'nullable|string|max:100',
            'license_expiry' => 'nullable|date|after:today',
            'vehicle_model' => 'nullable|string|max:100',
            'vehicle_year' => 'nullable|integer|min:1900|max:' . (date('Y') + 1),
            'vehicle_color' => 'nullable|string|max:50',
            'license_plate' => 'nullable|string|max:20',
        ];

        // Add driver-specific validation if user is a driver
        if ($user->role === 'driver') {
            $driverRules = [
                'license_number' => 'required|string|max:255',
                'license_expiry' => 'required|date|after:today',
                'vehicle_model' => 'required|string|max:255',
                'vehicle_year' => 'required|integer|min:1900|max:' . (date('Y') + 1),
                'vehicle_color' => 'required|string|max:255',
                'license_plate' => 'required|string|max:255',
                'vehicle_seats' => 'required|integer|min:1|max:20',
            ];
            $rules = array_merge($rules, $driverRules);
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            // Handle profile picture upload
            if ($request->hasFile('profile_picture')) {
                $file = $request->file('profile_picture');

                // Verify file is a genuine image (not just MIME-spoofed)
                $tempPath = $file->getPathname();
                $imageInfo = @getimagesize($tempPath);
                if ($imageInfo === false) {
                    return back()->withErrors(['profile_picture' => 'The file is not a valid image.'])->withInput();
                }

                // Delete old profile picture if exists
                if ($user->profile_picture) {
                    Storage::disk('public')->delete($user->profile_picture);
                }

                // Store new profile picture
                $profilePicturePath = $file->store('profile_pictures', 'public');
                $user->profile_picture = $profilePicturePath;
            }

            // Update basic information
            $user->name = $request->first_name . ' ' . $request->last_name;
            $user->email = $request->email;
            $user->phone = $request->phone;
            $user->date_of_birth = $request->date_of_birth;
            $user->address = $request->address;
            $user->emergency_contact_name = $request->emergency_contact_name;
            $user->emergency_contact_phone = $request->emergency_contact_phone;
            $user->emergency_contact_relationship = $request->emergency_contact_relationship;

            // Update driver information if applicable
            if ($user->role === 'driver') {
                $user->license_number = $request->license_number;
                $user->license_expiry = $request->license_expiry;
                $user->vehicle_model = $request->vehicle_model;
                $user->vehicle_year = $request->vehicle_year;
                $user->vehicle_color = $request->vehicle_color;
                $user->license_plate = $request->license_plate;
                $user->vehicle_seats = $request->vehicle_seats;
            }

            $user->save();

            return back()->with('success', 'Profile updated successfully!');

        } catch (\Exception $e) {
            return back()->with('error', 'An error occurred while updating your profile. Please try again.');
        }
    }
}
