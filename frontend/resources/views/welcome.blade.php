@extends('layouts.app')

@section('title', 'Welcome')

@section('content')
<div class="min-h-screen bg-brand-warm flex items-center justify-center py-12 px-4">
    <div class="bg-white rounded-2xl shadow-sm border border-brand-border p-8 w-full max-w-md mx-auto text-center">
        @php use Illuminate\Support\Facades\Auth; @endphp
        @if(Auth::check())
            <p class="text-xl text-brand-navy font-semibold mb-6">Welcome, {{ Auth::user()->name }}!</p>
            <form method="POST" action="/logout">
                @csrf
                <button type="submit" class="bg-brand-amber hover:bg-brand-amber-600 text-white font-semibold py-3 px-6 rounded-brand transition">Logout</button>
            </form>
        @else
            <a href="/login" class="text-brand-amber hover:text-brand-amber-600 hover:underline font-medium">Login</a>
        @endif
    </div>
</div>
@endsection
