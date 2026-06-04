@props(['tripType' => 'go'])

<span class="inline-flex items-center px-2 py-0.5 text-xs font-semibold rounded-full {{ $tripType === 'return' ? 'bg-amber-100 text-amber-800' : 'bg-brand-amber text-white' }}">
    {{ strtoupper($tripType) }} TRIP
</span>
