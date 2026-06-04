@extends('layouts.app')

@section('title', $user->name . ' - Driver Profile - Huber')

@section('content')
<div class="min-h-screen bg-brand-warm py-8">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Back Button -->
        <div class="mb-4">
            <a href="{{ url()->previous() }}" class="inline-flex items-center gap-2 border border-brand-border text-brand-navy px-4 py-2 rounded-brand hover:bg-brand-amber-light/50 transition">
                <i class="fas fa-arrow-left mr-2"></i>Back
            </a>
        </div>

        <!-- Driver Header -->
        <div class="bg-white rounded-2xl border border-brand-border overflow-hidden mb-6">
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-center">
                    <div class="md:col-span-3 text-center">
                        @if($user->profile_picture)
                            <img src="{{ asset('storage/' . $user->profile_picture) }}" 
                                 alt="Profile" class="rounded-full mx-auto mb-3 w-[120px] h-[120px] object-cover border-4 border-white shadow-lg">
                        @else
                            <div class="bg-brand-navy text-white rounded-full flex items-center justify-center mx-auto mb-3 w-[120px] h-[120px] text-5xl">
                                <i class="fas fa-user"></i>
                            </div>
                        @endif
                    </div>
                    <div class="md:col-span-6">
                        <h2 class="text-2xl font-bold text-brand-navy mb-2">{{ $user->name }}</h2>
                        <p class="text-brand-navy/60 mb-3">
                            <i class="fas fa-car mr-2"></i>Professional Driver
                        </p>
                        
                        <!-- Rating Display -->
                        <div class="flex items-center mb-3">
                            <div class="mr-2">
                                <x-star-rating :rating="$averageOverallRating" />
                            </div>
                            <div class="ml-2">
                                <div class="font-bold text-brand-navy">{{ number_format($averageOverallRating, 1) }}/5</div>
                                <small class="text-brand-navy/60">{{ $totalReviews }} reviews</small>
                            </div>
                        </div>

                        <!-- Quick Stats -->
                        <div class="grid grid-cols-3 gap-4 text-center">
                            <div>
                                <div class="font-bold text-brand-amber text-lg">{{ $previousRides->count() }}</div>
                                <small class="text-brand-navy/60">Completed Rides</small>
                            </div>
                            <div>
                                <div class="font-bold text-green-600 text-lg">{{ $filteredAvailableRides->count() }}</div>
                                <small class="text-brand-navy/60">Available Rides</small>
                            </div>
                            <div>
                                <div class="font-bold text-blue-600 text-lg">{{ $totalReviews }}</div>
                                <small class="text-brand-navy/60">Reviews</small>
                            </div>
                        </div>
                    </div>
                    <div class="md:col-span-3 text-right">
                        <div class="mb-3">
                            <span class="inline-flex items-center gap-1 px-3 py-1.5 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                <i class="fas fa-check-circle"></i>Verified Driver
                            </span>
                        </div>
                        @if($user->license_number)
                            <div class="text-sm text-brand-navy/60">
                                <i class="fas fa-id-card mr-1"></i>License: {{ $user->license_number }}
                            </div>
                        @endif
                        @if($user->vehicle_model)
                            <div class="text-sm text-brand-navy/60">
                                <i class="fas fa-car mr-1"></i>{{ $user->vehicle_model }} ({{ $user->vehicle_year }})
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Vehicle Photos -->
        @if($driverDocuments && ($driverDocuments->vehicle_photo_1 || $driverDocuments->vehicle_photo_2 || $driverDocuments->vehicle_photo_3))
        <div class="bg-white rounded-2xl border border-brand-border overflow-hidden mb-6">
            <div class="bg-brand-navy text-white text-center px-6 py-4">
                <h5 class="text-lg font-semibold"><i class="fas fa-images mr-2"></i>Vehicle Photos</h5>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    @if($driverDocuments->vehicle_photo_1)
                    <div class="text-center">
                        <img src="{{ asset('storage/' . $driverDocuments->vehicle_photo_1) }}" 
                             alt="Vehicle Front View" class="w-full max-h-[200px] object-cover rounded-xl border border-brand-border">
                        <div class="mt-2">
                            <small class="text-brand-navy/60">Front View</small>
                        </div>
                    </div>
                    @endif
                    @if($driverDocuments->vehicle_photo_2)
                    <div class="text-center">
                        <img src="{{ asset('storage/' . $driverDocuments->vehicle_photo_2) }}" 
                             alt="Vehicle Side View" class="w-full max-h-[200px] object-cover rounded-xl border border-brand-border">
                        <div class="mt-2">
                            <small class="text-brand-navy/60">Side View</small>
                        </div>
                    </div>
                    @endif
                    @if($driverDocuments->vehicle_photo_3)
                    <div class="text-center">
                        <img src="{{ asset('storage/' . $driverDocuments->vehicle_photo_3) }}" 
                             alt="Vehicle Rear View" class="w-full max-h-[200px] object-cover rounded-xl border border-brand-border">
                        <div class="mt-2">
                            <small class="text-brand-navy/60">Rear View</small>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @endif

        <!-- Rating Distribution -->
        @if($totalReviews > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div class="bg-white rounded-2xl border border-brand-border overflow-hidden">
                <div class="bg-brand-warm text-center px-6 py-4 border-b border-brand-border">
                    <h5 class="text-lg font-semibold text-brand-navy"><i class="fas fa-chart-bar mr-2"></i>Rating Distribution</h5>
                </div>
                <div class="p-6">
                    @php
                        $barCss = '<style>';
                        for($i = 5; $i >= 1; $i--) {
                            $pct = (($ratingDistribution[$i] ?? 0) / $totalReviews) * 100;
                            $barCss .= ".rating-bar-{$i}{width:" . number_format($pct, 1) . "%;}";
                        }
                        $barCss .= "</style>\n";
                        echo $barCss;
                    @endphp
                    @for($i = 5; $i >= 1; $i--)
                        <div class="flex items-center mb-3">
                            <div class="w-[60px] text-amber-400 font-medium">{{ $i }} ★</div>
                            <div class="flex-1 mx-3">
                                <div class="w-full bg-brand-border rounded-full h-2 overflow-hidden">
                                    <div class="bg-amber-400 h-full rounded-full transition-all rating-bar-{{ $i }}"></div>
                                </div>
                            </div>
                            <div class="w-10 text-right text-brand-navy/60 text-sm">{{ $ratingDistribution[$i] ?? 0 }}</div>
                        </div>
                    @endfor
                </div>
            </div>
            
            <div class="bg-white rounded-2xl border border-brand-border overflow-hidden">
                <div class="bg-brand-warm text-center px-6 py-4 border-b border-brand-border">
                    <h5 class="text-lg font-semibold text-brand-navy"><i class="fas fa-chart-pie mr-2"></i>Category Ratings</h5>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="text-center">
                            <div class="text-3xl font-bold text-brand-amber mb-1">{{ number_format($averageDriverRating, 1) }}</div>
                            <small class="text-brand-navy/60">Driver</small>
                        </div>
                        <div class="text-center">
                            <div class="text-3xl font-bold text-amber-400 mb-1">{{ number_format($averageVehicleRating, 1) }}</div>
                            <small class="text-brand-navy/60">Vehicle</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Available Rides -->
        @if($filteredAvailableRides->isNotEmpty() || $filteredReturnRides->isNotEmpty())
        <div class="bg-white rounded-2xl border border-brand-border overflow-hidden mb-6">
            <div class="bg-green-600 text-white text-center px-6 py-4">
                <h5 class="text-lg font-semibold"><i class="fas fa-calendar-check mr-2"></i>Available Rides</h5>
            </div>
            <div class="p-6">
                <!-- Go Trips -->
                @if($filteredAvailableRides->isNotEmpty())
                <div class="mb-6">
                    <h6 class="font-semibold text-brand-amber mb-4"><i class="fas fa-arrow-right mr-2"></i>Go Trips</h6>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($filteredAvailableRides as $ride)
                            <x-ride-card :ride="$ride" type="go" />
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Return Trips -->
                @if($filteredReturnRides->isNotEmpty())
                <div>
                    <h6 class="font-semibold text-amber-500 mb-4"><i class="fas fa-arrow-left mr-2"></i>Return Trips</h6>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($filteredReturnRides as $ride)
                            <x-ride-card :ride="$ride" type="return" />
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
        </div>
        @endif

        <!-- Previous Rides -->
        @if($previousRides->isNotEmpty())
        <div class="bg-white rounded-2xl border border-brand-border overflow-hidden mb-6">
            <div class="bg-blue-600 text-white text-center px-6 py-4">
                <h5 class="text-lg font-semibold"><i class="fas fa-history mr-2"></i>Previous Rides</h5>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($previousRides as $ride)
                        <x-ride-card :ride="$ride" type="completed" />
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        <!-- Reviews -->
        @if($reviews->isNotEmpty())
        <div class="bg-white rounded-2xl border border-brand-border overflow-hidden">
            <div class="bg-amber-400 text-brand-navy text-center px-6 py-4">
                <h5 class="text-lg font-semibold"><i class="fas fa-star mr-2"></i>Customer Reviews ({{ $totalReviews }})</h5>
            </div>
            <div class="p-6">
                <div class="space-y-4">
                    @foreach($reviews->take(6) as $review)
                    <div class="bg-white rounded-xl border border-brand-border overflow-hidden">
                        <div class="p-4">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div class="md:col-span-2">
                                    <!-- Customer Info -->
                                    <div class="flex items-center mb-3">
                                        @if($review->user && $review->user->profile_picture)
                                            <img src="{{ asset('storage/' . $review->user->profile_picture) }}" 
                                                 alt="Profile" class="rounded-full mr-3 w-12 h-12 object-cover">
                                        @else
                                            <div class="bg-brand-navy/20 text-brand-navy/60 rounded-full flex items-center justify-center mr-3 w-12 h-12">
                                                <i class="fas fa-user"></i>
                                            </div>
                                        @endif
                                        <div>
                                            <h6 class="font-semibold text-brand-navy mb-0.5">{{ $review->user ? $review->user->name : 'Anonymous' }}</h6>
                                            <small class="text-brand-navy/60">
                                                {{ $review->created_at->format('M d, Y') }}
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 ml-2">{{ \Illuminate\Support\Str::upper($review->trip_type) }} TRIP</span>
                                            </small>
                                        </div>
                                    </div>

                                    <!-- Ride Details -->
                                    <div class="mb-3">
                                        <div class="flex items-center text-sm">
                                            <i class="fas fa-map-marker-alt text-brand-amber mr-2"></i>
                                            <span class="font-semibold text-brand-navy">
                                                {{ $review->trip_type === 'return' ? optional($review->ride)->destination : optional($review->ride)->station_location }}
                                            </span>
                                            <i class="fas fa-arrow-right mx-2 text-brand-navy/40"></i>
                                            <span class="font-semibold text-brand-navy">
                                                {{ $review->trip_type === 'return' ? optional($review->ride)->station_location : optional($review->ride)->destination }}
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Review Text -->
                                    @if($review->review_text)
                                        <div class="mb-3">
                                            <p class="text-brand-navy/80 italic">&ldquo;{{ $review->review_text }}&rdquo;</p>
                                        </div>
                                    @endif
                                </div>

                                <div class="md:col-span-1">
                                    <!-- Overall Rating -->
                                    <div class="text-center mb-3">
                                        <div class="mb-1">
                                            <x-star-rating :rating="$review->overall_rating" />
                                        </div>
                                        <div class="font-bold text-brand-navy">{{ $review->overall_rating }}/5</div>
                                        <small class="text-brand-navy/60">Overall Rating</small>
                                    </div>

                                    <!-- Category Ratings -->
                                    <div class="grid grid-cols-2 gap-2 text-center">
                                        <div>
                                            <div class="text-sm text-brand-navy/60">Driver</div>
                                            <div class="font-semibold text-brand-amber">{{ $review->driver_rating }}/5</div>
                                        </div>
                                        <div>
                                            <div class="text-sm text-brand-navy/60">Vehicle</div>
                                            <div class="font-semibold text-amber-400">{{ $review->vehicle_rating }}/5</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
