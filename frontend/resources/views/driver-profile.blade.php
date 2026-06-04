@extends('layouts.app')

@section('title', 'Driver Profile - Huber')

@section('content')
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-6">
        <a href="{{ route('home') }}" class="inline-flex items-center gap-2 border border-brand-border text-brand-navy px-4 py-2 rounded-brand hover:bg-brand-amber-light/50 transition">
            <i class="fas fa-arrow-left mr-2"></i>Back to Home
        </a>
    </div>

    <div class="bg-white rounded-2xl border border-brand-border overflow-hidden">
        <div class="bg-brand-navy text-white px-6 py-4">
            <h3 class="text-lg font-semibold">
                <i class="fas fa-car mr-2"></i>Driver Profile
            </h3>
        </div>
        <div class="p-6">
            <x-flash-message type="success" :dismissible="true" />
            <x-flash-message type="error" :dismissible="true" />

            <!-- Legal Documents Section (Read-Only) -->
            <h5 class="text-brand-amber font-semibold mb-4">
                <i class="fas fa-file-alt mr-2"></i>Legal Documents
            </h5>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div class="bg-brand-warm border border-brand-border rounded-xl p-4 text-center min-h-[250px] flex flex-col justify-between">
                    <h6 class="text-brand-navy/60 font-medium mb-2">Driver's License</h6>
                    @if($driverDocuments && $driverDocuments->driver_license_file)
                        <div class="border border-brand-border rounded-lg overflow-hidden bg-white">
                            <img src="{{ asset('storage/' . $driverDocuments->driver_license_file) }}" 
                                 alt="Driver's License" 
                                 class="w-full max-h-[200px] object-cover">
                        </div>
                    @else
                        <div class="flex flex-col items-center justify-center h-[200px] bg-brand-border/30 rounded-lg">
                            <i class="fas fa-file-alt text-brand-navy/40" style="font-size: 3rem;"></i>
                            <p class="text-brand-navy/60 mt-2">No document uploaded</p>
                        </div>
                    @endif
                </div>
                
                <div class="bg-brand-warm border border-brand-border rounded-xl p-4 text-center min-h-[250px] flex flex-col justify-between">
                    <h6 class="text-brand-navy/60 font-medium mb-2">Vehicle Registration</h6>
                    @if($driverDocuments && $driverDocuments->vehicle_registration_file)
                        <div class="border border-brand-border rounded-lg overflow-hidden bg-white">
                            <img src="{{ asset('storage/' . $driverDocuments->vehicle_registration_file) }}" 
                                 alt="Vehicle Registration" 
                                 class="w-full max-h-[200px] object-cover">
                        </div>
                    @else
                        <div class="flex flex-col items-center justify-center h-[200px] bg-brand-border/30 rounded-lg">
                            <i class="fas fa-file-alt text-brand-navy/40" style="font-size: 3rem;"></i>
                            <p class="text-brand-navy/60 mt-2">No document uploaded</p>
                        </div>
                    @endif
                </div>
                
                <div class="bg-brand-warm border border-brand-border rounded-xl p-4 text-center min-h-[250px] flex flex-col justify-between">
                    <h6 class="text-brand-navy/60 font-medium mb-2">Insurance Certificate</h6>
                    @if($driverDocuments && $driverDocuments->insurance_certificate_file)
                        <div class="border border-brand-border rounded-lg overflow-hidden bg-white">
                            <img src="{{ asset('storage/' . $driverDocuments->insurance_certificate_file) }}" 
                                 alt="Insurance Certificate" 
                                 class="w-full max-h-[200px] object-cover">
                        </div>
                    @else
                        <div class="flex flex-col items-center justify-center h-[200px] bg-brand-border/30 rounded-lg">
                            <i class="fas fa-file-alt text-brand-navy/40" style="font-size: 3rem;"></i>
                            <p class="text-brand-navy/60 mt-2">No document uploaded</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Vehicle Photos Section -->
            <hr class="my-6 border-brand-border">
            <h5 class="text-brand-amber font-semibold mb-4">
                <i class="fas fa-images mr-2"></i>Vehicle Photos
            </h5>
            <form method="POST" action="{{ route('driver.vehicle-photos.update') }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                    <div class="bg-brand-warm border border-brand-border rounded-xl p-4 text-center">
                        <h6 class="text-brand-navy/60 font-medium mb-2">Vehicle Photo 1</h6>
                        @if($driverDocuments && $driverDocuments->vehicle_photo_1)
                            <div class="border border-brand-border rounded-lg overflow-hidden bg-white mb-2">
                                <img src="{{ asset('storage/' . $driverDocuments->vehicle_photo_1) }}" 
                                     alt="Vehicle Photo 1" 
                                     class="w-full max-h-[150px] object-cover">
                            </div>
                        @endif
                        <input type="file" class="w-full px-4 py-3 border border-brand-border rounded-brand text-brand-navy file:mr-4 file:py-2 file:px-4 file:rounded-brand file:border-0 file:text-sm file:font-semibold file:bg-brand-amber file:text-white hover:file:bg-brand-amber-600 transition @error('vehicle_photo_1') border-red-500 @enderror" 
                               name="vehicle_photo_1" accept="image/*">
                        @error('vehicle_photo_1')
                            <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                        @enderror
                        <div class="text-sm text-brand-navy/60 mt-1">Front view</div>
                    </div>
                    
                    <div class="bg-brand-warm border border-brand-border rounded-xl p-4 text-center">
                        <h6 class="text-brand-navy/60 font-medium mb-2">Vehicle Photo 2</h6>
                        @if($driverDocuments && $driverDocuments->vehicle_photo_2)
                            <div class="border border-brand-border rounded-lg overflow-hidden bg-white mb-2">
                                <img src="{{ asset('storage/' . $driverDocuments->vehicle_photo_2) }}" 
                                     alt="Vehicle Photo 2" 
                                     class="w-full max-h-[150px] object-cover">
                            </div>
                        @endif
                        <input type="file" class="w-full px-4 py-3 border border-brand-border rounded-brand text-brand-navy file:mr-4 file:py-2 file:px-4 file:rounded-brand file:border-0 file:text-sm file:font-semibold file:bg-brand-amber file:text-white hover:file:bg-brand-amber-600 transition @error('vehicle_photo_2') border-red-500 @enderror" 
                               name="vehicle_photo_2" accept="image/*">
                        @error('vehicle_photo_2')
                            <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                        @enderror
                        <div class="text-sm text-brand-navy/60 mt-1">Side view</div>
                    </div>
                    
                    <div class="bg-brand-warm border border-brand-border rounded-xl p-4 text-center">
                        <h6 class="text-brand-navy/60 font-medium mb-2">Vehicle Photo 3</h6>
                        @if($driverDocuments && $driverDocuments->vehicle_photo_3)
                            <div class="border border-brand-border rounded-lg overflow-hidden bg-white mb-2">
                                <img src="{{ asset('storage/' . $driverDocuments->vehicle_photo_3) }}" 
                                     alt="Vehicle Photo 3" 
                                     class="w-full max-h-[150px] object-cover">
                            </div>
                        @endif
                        <input type="file" class="w-full px-4 py-3 border border-brand-border rounded-brand text-brand-navy file:mr-4 file:py-2 file:px-4 file:rounded-brand file:border-0 file:text-sm file:font-semibold file:bg-brand-amber file:text-white hover:file:bg-brand-amber-600 transition @error('vehicle_photo_3') border-red-500 @enderror" 
                               name="vehicle_photo_3" accept="image/*">
                        @error('vehicle_photo_3')
                            <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                        @enderror
                        <div class="text-sm text-brand-navy/60 mt-1">Rear view</div>
                    </div>
                </div>
                
                <div class="flex justify-end mb-4">
                    <button type="submit" class="inline-flex items-center gap-2 bg-brand-amber hover:bg-brand-amber-600 text-white font-semibold px-6 py-3 rounded-brand transition">
                        <i class="fas fa-save mr-2"></i>Update Vehicle Photos
                    </button>
                </div>
            </form>

            <!-- Vehicle Information Section -->
            <hr class="my-6 border-brand-border">
            <h5 class="text-brand-amber font-semibold mb-4">
                <i class="fas fa-info-circle mr-2"></i>Vehicle Information
            </h5>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-brand-navy font-semibold mb-1">License Plate</label>
                    <p class="px-4 py-3 bg-brand-warm border border-brand-border rounded-lg text-brand-navy">{{ $user->license_plate ?? 'Not provided' }}</p>
                </div>
                
                <div>
                    <label class="block text-brand-navy font-semibold mb-1">Vehicle Color</label>
                    <p class="px-4 py-3 bg-brand-warm border border-brand-border rounded-lg text-brand-navy">{{ $user->vehicle_color ?? 'Not provided' }}</p>
                </div>
                
                <div>
                    <label class="block text-brand-navy font-semibold mb-1">Vehicle Model</label>
                    <p class="px-4 py-3 bg-brand-warm border border-brand-border rounded-lg text-brand-navy">{{ $user->vehicle_model ?? 'Not provided' }}</p>
                </div>
                
                <div>
                    <label class="block text-brand-navy font-semibold mb-1">Vehicle Year</label>
                    <p class="px-4 py-3 bg-brand-warm border border-brand-border rounded-lg text-brand-navy">{{ $user->vehicle_year ?? 'Not provided' }}</p>
                </div>
                
                <div>
                    <label class="block text-brand-navy font-semibold mb-1">Number of Seats</label>
                    <p class="px-4 py-3 bg-brand-warm border border-brand-border rounded-lg text-brand-navy">{{ $user->vehicle_seats ?? 'Not provided' }}</p>
                </div>
                
                <div>
                    <label class="block text-brand-navy font-semibold mb-1">Driver's License Number</label>
                    <p class="px-4 py-3 bg-brand-warm border border-brand-border rounded-lg text-brand-navy">{{ $user->license_number ?? 'Not provided' }}</p>
                </div>
                
                <div>
                    <label class="block text-brand-navy font-semibold mb-1">License Expiry Date</label>
                    <p class="px-4 py-3 bg-brand-warm border border-brand-border rounded-lg text-brand-navy">{{ $user->license_expiry ?? 'Not provided' }}</p>
                </div>
            </div>

            <hr class="my-6 border-brand-border">
            <div class="flex justify-end">
                <a href="{{ route('user.profile') }}" class="inline-flex items-center gap-2 border border-brand-border text-brand-navy px-6 py-3 rounded-brand hover:bg-brand-amber-light/50 transition font-medium">
                    <i class="fas fa-edit mr-2"></i>Edit Personal Profile
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
