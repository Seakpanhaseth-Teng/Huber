@props(['tripType' => 'go'])

<span class="badge {{ $tripType === 'return' ? 'bg-warning' : 'bg-primary' }}">
    {{ strtoupper($tripType) }} TRIP
</span>
