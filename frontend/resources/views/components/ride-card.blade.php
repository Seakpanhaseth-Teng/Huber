@props(['ride', 'type' => 'go'])

@php
    $isGo = $type === 'go';
    $isReturn = $type === 'return';
    $isCompleted = $type === 'completed';

    $origin = $isReturn ? ($ride->destination ?? '') : ($ride->station_location ?? '');
    $dest   = $isReturn ? ($ride->station_location ?? '') : ($ride->destination ?? '');
    $date   = $isReturn ? $ride->return_date : $ride->date;
    $time   = $isReturn ? $ride->return_time : $ride->time;
    $isExclusive = $isReturn ? $ride->return_is_exclusive : $ride->is_exclusive;
    $seats  = $isReturn ? $ride->return_available_seats : $ride->available_seats;
    $exclPrice  = $isReturn ? $ride->return_exclusive_price : $ride->go_to_exclusive_price;
    $sharedPrice = $isReturn ? $ride->return_price_per_person : $ride->go_to_price_per_person;
    $completionStatus = $isReturn ? $ride->return_completion_status : $ride->go_completion_status;
@endphp

@if($isCompleted)
    {{-- Completed Ride Card --}}
    <div class="bg-white rounded-xl border border-brand-border overflow-hidden hover:shadow-md transition-shadow">
        <div class="p-4">
            <div class="flex items-start justify-between mb-2">
                <h6 class="font-semibold text-blue-600">
                    <i class="fas fa-map-marker-alt mr-1"></i>{{ $origin }}
                </h6>
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                    <i class="fas fa-check-circle"></i>Completed
                </span>
            </div>
            <div class="text-center mb-2 text-brand-navy/40"><i class="fas fa-arrow-down"></i></div>
            <h6 class="font-semibold text-blue-600 mb-2">
                <i class="fas fa-map-marker-alt mr-1"></i>{{ $dest }}
            </h6>
            <div class="grid grid-cols-2 gap-2 text-center mb-2">
                <div>
                    <small class="text-brand-navy/60 block">Date</small>
                    <span class="font-semibold text-brand-navy text-sm">{{ $date ? $date->format('M d, Y') : '-' }}</span>
                </div>
                <div>
                    <small class="text-brand-navy/60 block">Time</small>
                    <span class="font-semibold text-brand-navy text-sm">{{ $time ? $time->format('H:i') : '-' }}</span>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-2 text-center">
                <div>
                    <small class="text-brand-navy/60 block">Type</small>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $isExclusive ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' }}">
                        {{ $isExclusive ? 'EXCLUSIVE' : 'SHARED' }}
                    </span>
                </div>
                <div>
                    <small class="text-brand-navy/60 block">Price</small>
                    <span class="font-semibold text-blue-600">
                        @if($isExclusive) ${{ number_format($exclPrice, 2) }}
                        @else ${{ number_format($sharedPrice, 2) }}/seat
                        @endif
                    </span>
                </div>
            </div>
        </div>
    </div>
@else
    {{-- Available Ride Card (Go or Return) --}}
    @php
        $headingText = $isGo ? 'text-brand-amber' : 'text-amber-500';
        $buttonBg = $isGo ? 'bg-brand-amber hover:bg-brand-amber-600' : 'bg-amber-500 hover:bg-amber-600';
        $priceText = $isGo ? 'text-brand-amber' : 'text-amber-500';
        $arrowIcon = $isGo ? 'fas fa-arrow-right' : 'fas fa-arrow-left';
        $tripType = $isGo ? 'go' : 'return';
    @endphp
    <div class="bg-white rounded-xl border border-brand-border overflow-hidden hover:shadow-md transition-shadow">
        <div class="p-4">
            <div class="flex items-start justify-between mb-2">
                <h6 class="font-semibold {{ $headingText }}">
                    <i class="fas fa-map-marker-alt mr-1"></i>{{ $origin }}
                </h6>
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $isExclusive ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' }}">
                    {{ $isExclusive ? 'EXCLUSIVE' : 'SHARED' }}
                </span>
            </div>
            <div class="text-center mb-2 text-brand-navy/40"><i class="{{ $arrowIcon }}"></i></div>
            <h6 class="font-semibold {{ $headingText }} mb-2">
                <i class="fas fa-map-marker-alt mr-1"></i>{{ $dest }}
            </h6>
            <div class="grid grid-cols-2 gap-2 text-center mb-2">
                <div>
                    <small class="text-brand-navy/60 block">Date</small>
                    <span class="font-semibold text-brand-navy text-sm">{{ $date ? $date->format('M d, Y') : '-' }}</span>
                </div>
                <div>
                    <small class="text-brand-navy/60 block">Time</small>
                    <span class="font-semibold text-brand-navy text-sm">{{ $time ? $time->format('H:i') : '-' }}</span>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-2 text-center mb-3">
                <div>
                    <small class="text-brand-navy/60 block">Available Seats</small>
                    @if($isExclusive)
                        <span class="font-semibold text-green-600">Exclusive</span>
                    @else
                        <span class="font-semibold text-green-600">{{ $seats }}</span>
                    @endif
                </div>
                <div>
                    <small class="text-brand-navy/60 block">Price</small>
                    <span class="font-semibold {{ $priceText }}">
                        @if($isExclusive) ${{ number_format($exclPrice, 2) }}
                        @else ${{ number_format($sharedPrice, 2) }}/seat
                        @endif
                    </span>
                </div>
            </div>
            @if($completionStatus === 'pending')
                @if($isExclusive)
                    <a href="{{ route('booking.payment', ['rideId' => $ride->id, 'tripType' => $tripType]) }}"
                       class="block text-center {{ $buttonBg }} text-white text-sm font-semibold py-2 px-4 rounded-brand transition">
                        <i class="fas fa-credit-card mr-1"></i>Book Exclusive
                    </a>
                @else
                    <a href="{{ route('booking.seat-selection', ['rideId' => $ride->id, 'tripType' => $tripType]) }}"
                       class="block text-center {{ $buttonBg }} text-white text-sm font-semibold py-2 px-4 rounded-brand transition">
                        <i class="fas fa-bookmark mr-1"></i>Select Seats
                    </a>
                @endif
            @else
                <div class="text-center">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-600">Not Available</span>
                </div>
            @endif
        </div>
    </div>
@endif
