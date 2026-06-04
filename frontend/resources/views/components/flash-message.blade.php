@props(['type' => 'success', 'message' => '', 'dismissible' => false])

@php
    $isSuccess = $type === 'success';
    $icon = $isSuccess ? 'fa-check-circle' : 'fa-exclamation-circle';
    $borderClass = $isSuccess ? 'border-brand-amber-light bg-brand-amber-light/20 text-brand-amber-dark' : 'border-red-300 bg-red-50 text-red-700';
    $iconColor = $isSuccess ? 'text-brand-amber' : 'text-red-500';
@endphp

@if(session($type))
    <div class="flex items-center gap-2 border rounded-lg px-4 py-3 mx-4 mt-4 {{ $borderClass }}" role="alert">
        <i class="fas {{ $icon }} {{ $iconColor }}"></i>
        <span>{{ session($type) }}</span>
        @if($dismissible)
        <button type="button" class="ml-auto text-current hover:opacity-70 cursor-pointer bg-transparent border-0 text-xl leading-none" data-dismiss="alert">&times;</button>
        @endif
    </div>
@endif
