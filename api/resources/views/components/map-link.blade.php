@props(['mapUrl' => '', 'location' => '', 'class' => 'btn btn-sm btn-outline-primary mt-1 ms-4'])

@php
    $href = $mapUrl ?: 'https://maps.google.com/?q=' . urlencode($location);
@endphp

@if($mapUrl || $location)
    <a href="{{ $href }}" target="_blank" rel="noopener noreferrer" class="{{ $class }}">
        <i class="fas fa-map-marker-alt me-1"></i>View on Map
    </a>
@endif
