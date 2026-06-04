@extends('layouts.app')

@section('title', 'Profile Management - Huber')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-6">
        <a href="{{ route('home') }}" class="inline-flex items-center gap-2 border border-brand-border text-brand-navy px-4 py-2 rounded-brand hover:bg-brand-amber-light/50 transition">
            <i class="fas fa-arrow-left mr-2"></i>Back to Home
        </a>
    </div>

    <div class="bg-white rounded-2xl border border-brand-border overflow-hidden">
        <div class="bg-brand-navy text-white px-6 py-4">
            <h3 class="text-lg font-semibold">
                <i class="fas fa-user-edit mr-2"></i>Profile Management
            </h3>
        </div>
        <div class="p-6">
            <x-flash-message type="success" :dismissible="true" />
            <x-flash-message type="error" :dismissible="true" />

            <form method="POST" action="{{ route('profile.update') }}" id="profileForm" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                
                <!-- Profile Picture Section -->
                <div class="mb-6">
                    <h5 class="text-brand-amber font-semibold mb-3">
                        <i class="fas fa-camera mr-2"></i>Profile Picture
                    </h5>
                    <div class="flex items-center gap-6">
                        <div class="flex-shrink-0">
                            @if($user->profile_picture)
                                <img src="{{ asset('storage/' . $user->profile_picture) }}" 
                                     alt="Profile Picture" 
                                     class="rounded-full w-24 h-24 object-cover border-2 border-brand-border">
                            @else
                                <div class="rounded-full bg-brand-navy/10 flex items-center justify-center w-24 h-24 border-2 border-brand-border">
                                    <i class="fas fa-user text-brand-navy/40" style="font-size: 2.5rem;"></i>
                                </div>
                            @endif
                        </div>
                        <div class="flex-1">
                            <label for="profile_picture" class="block text-brand-navy font-medium mb-1">Upload New Picture</label>
                            <input type="file" class="w-full px-4 py-3 border border-brand-border rounded-brand text-brand-navy file:mr-4 file:py-2 file:px-4 file:rounded-brand file:border-0 file:text-sm file:font-semibold file:bg-brand-amber file:text-white hover:file:bg-brand-amber-600 transition @error('profile_picture') border-red-500 @enderror" 
                                   id="profile_picture" name="profile_picture" accept="image/*">
                            @error('profile_picture')
                                <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                            @enderror
                            <div class="text-sm text-brand-navy/60 mt-1">JPG, PNG, GIF (max 2MB)</div>
                        </div>
                    </div>
                </div>

                <!-- Personal Information Section -->
                <h5 class="text-brand-amber font-semibold mb-3">
                    <i class="fas fa-user mr-2"></i>Personal Information
                </h5>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label for="first_name" class="block text-brand-navy font-medium mb-1">First Name</label>
                        <input type="text" class="w-full px-4 py-3 border border-brand-border rounded-brand text-brand-navy placeholder:text-brand-navy/40 focus:border-brand-amber focus:ring-2 focus:ring-brand-amber/20 outline-none transition @error('first_name') border-red-500 @enderror" 
                               id="first_name" name="first_name" 
                               value="{{ old('first_name', $user->first_name ?? '') }}" required>
                        @error('first_name')
                            <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div>
                        <label for="last_name" class="block text-brand-navy font-medium mb-1">Last Name</label>
                        <input type="text" class="w-full px-4 py-3 border border-brand-border rounded-brand text-brand-navy placeholder:text-brand-navy/40 focus:border-brand-amber focus:ring-2 focus:ring-brand-amber/20 outline-none transition @error('last_name') border-red-500 @enderror" 
                               id="last_name" name="last_name" 
                               value="{{ old('last_name', $user->last_name ?? '') }}" required>
                        @error('last_name')
                            <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label for="email" class="block text-brand-navy font-medium mb-1">
                            <i class="fas fa-envelope mr-1"></i>Email Address
                        </label>
                        <input type="email" class="w-full px-4 py-3 border border-brand-border rounded-brand text-brand-navy placeholder:text-brand-navy/40 focus:border-brand-amber focus:ring-2 focus:ring-brand-amber/20 outline-none transition @error('email') border-red-500 @enderror" 
                               id="email" name="email" 
                               value="{{ old('email', $user->email ?? '') }}" required>
                        @error('email')
                            <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div>
                        <label for="phone" class="block text-brand-navy font-medium mb-1">
                            <i class="fas fa-phone mr-1"></i>Phone Number
                        </label>
                        <input type="tel" class="w-full px-4 py-3 border border-brand-border rounded-brand text-brand-navy placeholder:text-brand-navy/40 focus:border-brand-amber focus:ring-2 focus:ring-brand-amber/20 outline-none transition @error('phone') border-red-500 @enderror" 
                               id="phone" name="phone" 
                               value="{{ old('phone', $user->phone ?? '') }}" required>
                        @error('phone')
                            <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label for="date_of_birth" class="block text-brand-navy font-medium mb-1">
                            <i class="fas fa-calendar mr-1"></i>Date of Birth
                        </label>
                        <input type="date" class="w-full px-4 py-3 border border-brand-border rounded-brand text-brand-navy focus:border-brand-amber focus:ring-2 focus:ring-brand-amber/20 outline-none transition @error('date_of_birth') border-red-500 @enderror" 
                               id="date_of_birth" name="date_of_birth" 
                               value="{{ old('date_of_birth', $user->date_of_birth ? $user->date_of_birth->format('Y-m-d') : '') }}">
                        @error('date_of_birth')
                            <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mb-4">
                    <label for="address" class="block text-brand-navy font-medium mb-1">
                        <i class="fas fa-map-marker-alt mr-1"></i>Address
                    </label>
                    <textarea class="w-full px-4 py-3 border border-brand-border rounded-brand text-brand-navy placeholder:text-brand-navy/40 focus:border-brand-amber focus:ring-2 focus:ring-brand-amber/20 outline-none transition @error('address') border-red-500 @enderror" 
                              id="address" name="address" rows="3" 
                              placeholder="Enter your full address">{{ old('address', $user->address ?? '') }}</textarea>
                    @error('address')
                        <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Emergency Contact Section -->
                <hr class="my-6 border-brand-border">
                <h5 class="text-brand-amber font-semibold mb-3">
                    <i class="fas fa-phone-alt mr-2"></i>Emergency Contact
                </h5>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                    <div>
                        <label for="emergency_contact_name" class="block text-brand-navy font-medium mb-1">Contact Name</label>
                        <input type="text" class="w-full px-4 py-3 border border-brand-border rounded-brand text-brand-navy placeholder:text-brand-navy/40 focus:border-brand-amber focus:ring-2 focus:ring-brand-amber/20 outline-none transition @error('emergency_contact_name') border-red-500 @enderror" 
                               id="emergency_contact_name" name="emergency_contact_name" 
                               value="{{ old('emergency_contact_name', $user->emergency_contact_name ?? '') }}">
                        @error('emergency_contact_name')
                            <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div>
                        <label for="emergency_contact_phone" class="block text-brand-navy font-medium mb-1">Contact Phone</label>
                        <input type="tel" class="w-full px-4 py-3 border border-brand-border rounded-brand text-brand-navy placeholder:text-brand-navy/40 focus:border-brand-amber focus:ring-2 focus:ring-brand-amber/20 outline-none transition @error('emergency_contact_phone') border-red-500 @enderror" 
                               id="emergency_contact_phone" name="emergency_contact_phone" 
                               value="{{ old('emergency_contact_phone', $user->emergency_contact_phone ?? '') }}">
                        @error('emergency_contact_phone')
                            <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div>
                        <label for="emergency_contact_relationship" class="block text-brand-navy font-medium mb-1">Relationship</label>
                        <select class="w-full px-4 py-3 border border-brand-border rounded-brand text-brand-navy focus:border-brand-amber focus:ring-2 focus:ring-brand-amber/20 outline-none transition @error('emergency_contact_relationship') border-red-500 @enderror" 
                                id="emergency_contact_relationship" name="emergency_contact_relationship">
                            <option value="">Select Relationship</option>
                            <option value="Spouse" {{ old('emergency_contact_relationship', $user->emergency_contact_relationship ?? '') == 'Spouse' ? 'selected' : '' }}>Spouse</option>
                            <option value="Parent" {{ old('emergency_contact_relationship', $user->emergency_contact_relationship ?? '') == 'Parent' ? 'selected' : '' }}>Parent</option>
                            <option value="Sibling" {{ old('emergency_contact_relationship', $user->emergency_contact_relationship ?? '') == 'Sibling' ? 'selected' : '' }}>Sibling</option>
                            <option value="Friend" {{ old('emergency_contact_relationship', $user->emergency_contact_relationship ?? '') == 'Friend' ? 'selected' : '' }}>Friend</option>
                            <option value="Other" {{ old('emergency_contact_relationship', $user->emergency_contact_relationship ?? '') == 'Other' ? 'selected' : '' }}>Other</option>
                        </select>
                        @error('emergency_contact_relationship')
                            <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                @if(auth()->user()->role === 'driver')
                    <hr class="my-6 border-brand-border">
                    <h5 class="text-brand-amber font-semibold mb-3">
                        <i class="fas fa-car mr-2"></i>Driver Information
                    </h5>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label for="license_number" class="block text-brand-navy font-medium mb-1">License Number</label>
                            <input type="text" class="w-full px-4 py-3 border border-brand-border rounded-brand text-brand-navy placeholder:text-brand-navy/40 focus:border-brand-amber focus:ring-2 focus:ring-brand-amber/20 outline-none transition @error('license_number') border-red-500 @enderror" 
                                   id="license_number" name="license_number" 
                                   value="{{ old('license_number', $user->license_number ?? '') }}">
                            @error('license_number')
                                <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div>
                            <label for="license_expiry" class="block text-brand-navy font-medium mb-1">License Expiry Date</label>
                            <input type="date" class="w-full px-4 py-3 border border-brand-border rounded-brand text-brand-navy focus:border-brand-amber focus:ring-2 focus:ring-brand-amber/20 outline-none transition @error('license_expiry') border-red-500 @enderror" 
                                   id="license_expiry" name="license_expiry" 
                                   value="{{ old('license_expiry', $user->license_expiry ?? '') }}">
                            @error('license_expiry')
                                <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label for="vehicle_model" class="block text-brand-navy font-medium mb-1">Vehicle Model</label>
                            <input type="text" class="w-full px-4 py-3 border border-brand-border rounded-brand text-brand-navy placeholder:text-brand-navy/40 focus:border-brand-amber focus:ring-2 focus:ring-brand-amber/20 outline-none transition @error('vehicle_model') border-red-500 @enderror" 
                                   id="vehicle_model" name="vehicle_model" 
                                   value="{{ old('vehicle_model', $user->vehicle_model ?? '') }}">
                            @error('vehicle_model')
                                <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div>
                            <label for="vehicle_year" class="block text-brand-navy font-medium mb-1">Vehicle Year</label>
                            <input type="number" class="w-full px-4 py-3 border border-brand-border rounded-brand text-brand-navy placeholder:text-brand-navy/40 focus:border-brand-amber focus:ring-2 focus:ring-brand-amber/20 outline-none transition @error('vehicle_year') border-red-500 @enderror" 
                                   id="vehicle_year" name="vehicle_year" 
                                   value="{{ old('vehicle_year', $user->vehicle_year ?? '') }}">
                            @error('vehicle_year')
                                <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label for="vehicle_color" class="block text-brand-navy font-medium mb-1">Vehicle Color</label>
                            <input type="text" class="w-full px-4 py-3 border border-brand-border rounded-brand text-brand-navy placeholder:text-brand-navy/40 focus:border-brand-amber focus:ring-2 focus:ring-brand-amber/20 outline-none transition @error('vehicle_color') border-red-500 @enderror" 
                                   id="vehicle_color" name="vehicle_color" 
                                   value="{{ old('vehicle_color', $user->vehicle_color ?? '') }}">
                            @error('vehicle_color')
                                <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div>
                            <label for="license_plate" class="block text-brand-navy font-medium mb-1">License Plate</label>
                            <input type="text" class="w-full px-4 py-3 border border-brand-border rounded-brand text-brand-navy placeholder:text-brand-navy/40 focus:border-brand-amber focus:ring-2 focus:ring-brand-amber/20 outline-none transition @error('license_plate') border-red-500 @enderror" 
                                   id="license_plate" name="license_plate" 
                                   value="{{ old('license_plate', $user->license_plate ?? '') }}">
                            @error('license_plate')
                                <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label for="vehicle_seats" class="block text-brand-navy font-medium mb-1">Number of Seats</label>
                            <input type="number" class="w-full px-4 py-3 border border-brand-border rounded-brand text-brand-navy placeholder:text-brand-navy/40 focus:border-brand-amber focus:ring-2 focus:ring-brand-amber/20 outline-none transition @error('vehicle_seats') border-red-500 @enderror" 
                                   id="vehicle_seats" name="vehicle_seats" 
                                   value="{{ old('vehicle_seats', $user->vehicle_seats ?? '') }}" min="1" max="20">
                            @error('vehicle_seats')
                                <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                @endif

                <hr class="my-6 border-brand-border">
                <!-- Action Buttons -->
                <div class="flex items-center justify-end gap-4">
                    <a href="{{ route('password.change') }}" class="inline-flex items-center gap-2 border border-amber-300 text-amber-700 px-6 py-3 rounded-brand hover:bg-amber-50 transition font-medium">
                        <i class="fas fa-lock mr-2"></i>Change Password
                    </a>
                    <button type="submit" class="inline-flex items-center gap-2 bg-brand-amber hover:bg-brand-amber-600 text-white font-semibold px-6 py-3 rounded-brand transition">
                        <i class="fas fa-save mr-2"></i>Update Profile
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
