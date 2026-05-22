@props(['type' => 'success', 'message' => '', 'dismissible' => false])

@php
    $isSuccess = $type === 'success';
    $icon = $isSuccess ? 'fa-check-circle' : 'fa-exclamation-circle';
    $alertClass = $isSuccess ? 'alert-success' : 'alert-danger';
    $iconColor = $isSuccess ? 'text-success' : 'text-danger';
@endphp

@if(session($type))
    <div class="alert {{ $alertClass }} alert-dismissible fade show" role="alert" style="border-radius: 12px;">
        <i class="fas {{ $icon }} {{ $iconColor }} me-2"></i>
        {{ session($type) }}
        @if($dismissible)
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        @endif
    </div>
@endif
