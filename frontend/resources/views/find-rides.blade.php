@extends('layouts.app')

@section('title', 'Find Your Perfect Ride')

@section('content')
<div class="min-h-screen bg-brand-warm py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-6">
            <h1 class="text-3xl font-bold text-brand-navy">Find Your Perfect Ride</h1>
            <p class="text-brand-navy/60 mt-2">Discover comfortable and affordable rides to your destination</p>
        </div>
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
            <!-- Search/Filter Panel -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-2xl border border-brand-border p-6">
                    <form method="GET" action="{{ route('find.rides') }}">
                        <div class="mb-4">
                            <label class="block text-brand-navy font-medium mb-1.5">From location</label>
                            <input type="text" name="from" class="w-full px-4 py-3 border border-brand-border rounded-brand text-brand-navy placeholder:text-brand-navy/40 focus:border-brand-amber focus:ring-2 focus:ring-brand-amber/20 outline-none transition" placeholder="From location" value="{{ $filters['from'] ?? '' }}">
                        </div>
                        <div class="mb-4">
                            <label class="block text-brand-navy font-medium mb-1.5">To location</label>
                            <input type="text" name="to" class="w-full px-4 py-3 border border-brand-border rounded-brand text-brand-navy placeholder:text-brand-navy/40 focus:border-brand-amber focus:ring-2 focus:ring-brand-amber/20 outline-none transition" placeholder="To location" value="{{ $filters['to'] ?? '' }}">
                        </div>
                        <div class="mb-4">
                            <label class="block text-brand-navy font-medium mb-1.5">Date</label>
                            <input type="date" name="date" class="w-full px-4 py-3 border border-brand-border rounded-brand text-brand-navy focus:border-brand-amber focus:ring-2 focus:ring-brand-amber/20 outline-none transition" value="{{ $filters['date'] ?? '' }}">
                        </div>
                        <hr class="border-brand-border mb-4">
                        <div class="mb-4">
                            <label class="block text-brand-navy font-medium mb-2">Ride Type</label>
                            <div class="flex items-center mb-2">
                                <input class="w-4 h-4 text-brand-amber border-brand-border rounded focus:ring-brand-amber" type="radio" name="rideType" id="allRides" value="all" {{ empty($filters['rideType']) || $filters['rideType'] === 'all' ? 'checked' : '' }}>
                                <label class="ml-2 text-brand-navy/80" for="allRides">Show all available rides</label>
                            </div>
                            <div class="flex items-center mb-2">
                                <input class="w-4 h-4 text-brand-amber border-brand-border rounded focus:ring-brand-amber" type="radio" name="rideType" id="sharedRide" value="shared" {{ ($filters['rideType'] ?? '') === 'shared' ? 'checked' : '' }}>
                                <label class="ml-2 text-brand-navy/80" for="sharedRide">Shared Ride</label>
                            </div>
                            <div class="flex items-center">
                                <input class="w-4 h-4 text-brand-amber border-brand-border rounded focus:ring-brand-amber" type="radio" name="rideType" id="exclusiveRide" value="exclusive" {{ ($filters['rideType'] ?? '') === 'exclusive' ? 'checked' : '' }}>
                                <label class="ml-2 text-brand-navy/80" for="exclusiveRide">Exclusive Ride</label>
                            </div>
                        </div>
                        <div class="mb-4">
                            <label class="block text-brand-navy font-medium mb-1.5">Price Range (USD)</label>
                            <div class="flex gap-2">
                                <input type="number" name="price_min" class="w-full px-4 py-3 border border-brand-border rounded-brand text-brand-navy placeholder:text-brand-navy/40 focus:border-brand-amber focus:ring-2 focus:ring-brand-amber/20 outline-none transition" placeholder="Min" value="{{ $filters['price_min'] ?? '' }}">
                                <input type="number" name="price_max" class="w-full px-4 py-3 border border-brand-border rounded-brand text-brand-navy placeholder:text-brand-navy/40 focus:border-brand-amber focus:ring-2 focus:ring-brand-amber/20 outline-none transition" placeholder="Max" value="{{ $filters['price_max'] ?? '' }}">
                            </div>
                        </div>
                        <div class="mb-4">
                            <label class="block text-brand-navy font-medium mb-1.5">Departure Time</label>
                            <select class="w-full px-4 py-3 border border-brand-border rounded-brand text-brand-navy focus:border-brand-amber focus:ring-2 focus:ring-brand-amber/20 outline-none transition appearance-none" name="departure_time">
                                <option value="" {{ empty($filters['departure_time']) ? 'selected' : '' }}>Any Time</option>
                                <option value="morning" {{ ($filters['departure_time'] ?? '') === 'morning' ? 'selected' : '' }}>Morning</option>
                                <option value="afternoon" {{ ($filters['departure_time'] ?? '') === 'afternoon' ? 'selected' : '' }}>Afternoon</option>
                                <option value="evening" {{ ($filters['departure_time'] ?? '') === 'evening' ? 'selected' : '' }}>Evening</option>
                            </select>
                        </div>
                        <div class="mb-2">
                            <label class="block text-brand-navy font-medium mb-1.5">Sort By</label>
                            <select class="w-full px-4 py-3 border border-brand-border rounded-brand text-brand-navy focus:border-brand-amber focus:ring-2 focus:ring-brand-amber/20 outline-none transition appearance-none" name="sort_by">
                                <option value="price_asc" {{ ($filters['sort_by'] ?? '') === 'price_asc' ? 'selected' : '' }}>Price: Low to High</option>
                                <option value="price_desc" {{ ($filters['sort_by'] ?? '') === 'price_desc' ? 'selected' : '' }}>Price: High to Low</option>
                            </select>
                        </div>
                        <button class="w-full bg-brand-amber hover:bg-brand-amber-600 text-white font-semibold py-3 px-6 rounded-brand transition mt-6" type="submit">Search Rides</button>
                        <a href="{{ route('find.rides') }}" class="block w-full text-center border border-brand-border text-brand-navy hover:bg-brand-amber-light/50 py-3 px-6 rounded-brand transition mt-2">Clear Filters</a>
                    </form>
                </div>
            </div>
            <!-- Rides List -->
            <div class="lg:col-span-3">
                <div class="flex items-center justify-between mb-4">
                    <h5 class="text-lg font-semibold text-brand-navy">Available Rides</h5>
                    <span class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-medium bg-brand-amber-light/30 text-brand-amber">{{ count($rideEntries) }} rides found</span>
                </div>
                <div class="space-y-6">
                @php
                    function getVehiclePhoto($entry) {
                        $driverDocs = $entry['user'] && $entry['user']->driverDocuments ? $entry['user']->driverDocuments : null;
                        if ($driverDocs && $driverDocs->vehicle_photo_1) {
                            return asset('storage/' . $driverDocs->vehicle_photo_1);
                        }
                        return 'https://source.unsplash.com/900x220/?car,road,travel';
                    }
                    function getProfilePhoto($entry) {
                        $user = $entry['user'];
                        if ($user && $user->profile_picture) {
                            return asset('storage/' . $user->profile_picture);
                        }
                        return 'https://ui-avatars.com/api/?name=' . urlencode($user ? $user->name : 'Driver') . '&background=E06810&color=fff';
                    }
                @endphp
                @forelse($rideEntries as $entry)
                    <div class="bg-white rounded-2xl border border-brand-border overflow-hidden hover:shadow-lg transition-shadow">
                        <div class="relative">
                            <img src="{{ getVehiclePhoto($entry) }}" class="w-full h-44 object-cover">
                            <div class="absolute top-0 start-0 p-4 flex items-center" style="z-index:2;">
                                <img src="{{ getProfilePhoto($entry) }}" class="rounded-full mr-2 border-2 border-white" width="48" height="48" alt="Driver Avatar">
                                <div>
                                    <a href="{{ route('driver.profile.public', ['driverId' => $entry['user'] ? $entry['user']->id : 0]) }}" class="bg-brand-amber text-white no-underline rounded-full font-semibold inline-block px-4 py-1.5 shadow-sm text-base hover:bg-brand-amber-600 transition">
                                        {{ $entry['user'] ? $entry['user']->name : 'Driver' }}
                                    </a>
                                    <div class="flex items-center gap-2 mt-1">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $entry['is_exclusive'] ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' }}">{{ $entry['is_exclusive'] ? 'EXCLUSIVE' : 'SHARED' }}</span>
                                        <span class="text-amber-400 text-sm">&#9733; 4.8</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="p-4">
                            <div class="bg-brand-amber-light/10 rounded-xl p-4 mb-3">
                                <div class="flex items-center mb-2">
                                    <i class="fas fa-map-marker-alt text-brand-amber mr-2"></i>
                                    <span class="font-semibold text-brand-navy">PICKUP</span>
                                    <span class="ml-2 text-brand-navy/80">{{ $entry['station_location'] }}</span>
                                    <span class="ml-auto text-brand-navy/60 text-sm"><i class="far fa-calendar-alt mr-1"></i>{{ $entry['date'] }} <i class="far fa-clock ml-2 mr-1"></i>{{ $entry['time'] }}</span>
                                </div>
                                <div class="flex items-center">
                                    <i class="fas fa-map-pin text-brand-navy/40 mr-2"></i>
                                    <span class="font-semibold text-brand-navy">DROPOFF</span>
                                    <span class="ml-2 text-brand-navy/80">{{ $entry['destination'] }}</span>
                                </div>
                            </div>
                            <div class="flex flex-wrap gap-3 items-center mb-3">
                                <span class="inline-flex items-center gap-1 px-3 py-1.5 rounded-full text-sm font-medium bg-brand-warm border border-brand-border text-brand-navy"><i class="fas fa-car mr-1"></i> {{ $entry['user'] && $entry['user']->vehicle_model ? $entry['user']->vehicle_model : 'Car Model' }} ({{ $entry['user'] && $entry['user']->vehicle_color ? $entry['user']->vehicle_color : 'Color' }})</span>
                                <span class="inline-flex items-center gap-1 px-3 py-1.5 rounded-full text-sm font-medium bg-brand-warm border border-brand-border text-brand-navy">
                                    <i class="fas fa-users mr-1"></i> 
                                    @if($entry['is_exclusive'])
                                        Exclusive
                                    @else
                                        {{ $entry['available_seats'] }} seats available
                                    @endif
                                </span>
                                <span class="inline-flex items-center gap-1 px-3 py-1.5 rounded-full text-sm font-medium bg-brand-warm border border-brand-border text-brand-navy">
                                    <i class="fas fa-dollar-sign mr-1"></i> 
                                    <span class="font-bold text-brand-amber">
                                        @if($entry['is_exclusive'])
                                            ${{ number_format($entry['price_per_person'], 2) }} (Total)
                                        @else
                                            ${{ number_format($entry['price_per_person'], 2) }}/person
                                        @endif
                                    </span>
                                </span>
                            </div>
                            <div class="flex justify-end">
                                @if($entry['is_exclusive'])
                                    @if($entry['has_booked'])
                                        <button class="border border-brand-border text-brand-navy hover:bg-brand-amber-light/50 px-6 py-2 rounded-brand transition font-medium text-sm" disabled>
                                            <i class="fas fa-check mr-2"></i>Booked
                                        </button>
                                    @else
                                        <a href="{{ route('payment.show', ['rideId' => $entry['ride']->id, 'tripType' => $entry['type'] === 'Back' ? 'return' : 'go']) }}" class="bg-brand-amber hover:bg-brand-amber-600 text-white font-semibold px-6 py-2 rounded-brand transition text-sm inline-flex items-center gap-2">
                                            <i class="fas fa-credit-card"></i>Book Now
                                        </a>
                                    @endif
                                @else
                                    @if($entry['has_booked'])
                                        <a href="{{ route('booking.seat-selection', ['rideId' => $entry['ride']->id, 'tripType' => $entry['type'] === 'Back' ? 'return' : 'go']) }}" class="bg-amber-500 hover:bg-amber-600 text-white font-semibold px-6 py-2 rounded-brand transition text-sm inline-flex items-center gap-2">
                                            <i class="fas fa-plus"></i>Book Another
                                        </a>
                                    @else
                                        <a href="{{ route('booking.seat-selection', ['rideId' => $entry['ride']->id, 'tripType' => $entry['type'] === 'Back' ? 'return' : 'go']) }}" class="bg-brand-amber hover:bg-brand-amber-600 text-white font-semibold px-6 py-2 rounded-brand transition text-sm inline-flex items-center gap-2">
                                            <i class="fas fa-chair"></i>Select Seats
                                        </a>
                                    @endif
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="flex items-center gap-3 border border-blue-300 bg-blue-50 text-blue-700 rounded-lg px-6 py-4">
                        <i class="fas fa-info-circle"></i>
                        <span>No rides found. Try adjusting your filters.</span>
                    </div>
                @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
