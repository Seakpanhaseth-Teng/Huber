@extends('layouts.app')

@section('title', 'Join Huber - Choose Role')

@section('content')
<div class="min-h-screen bg-brand-warm flex items-center justify-center py-12 px-4">
    <div class="bg-white rounded-2xl shadow-sm border border-brand-border p-8 w-full max-w-md mx-auto">
        <div class="flex flex-col items-center mb-6">
            <div class="bg-brand-amber-light/30 p-3 rounded-full mb-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-brand-amber" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 15c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
            </div>
            <h2 class="text-2xl font-bold text-brand-navy">Join Huber</h2>
            <p class="text-brand-navy/60 text-sm mt-1">Create your account and start your journey</p>
        </div>
        <div class="mb-6">
            <label class="block text-brand-navy/80 font-medium mb-3 text-center">Choose Your Role</label>
            <div class="flex gap-4 justify-center">
                <button onclick="window.location.href='/register/user'" class="flex flex-col items-center border-2 border-brand-border rounded-2xl px-8 py-6 focus:outline-none shadow-sm hover:shadow-md hover:scale-105 transition-all bg-white">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mb-3 text-brand-navy/60" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <circle cx="12" cy="7" r="4" stroke="currentColor" stroke-width="2" fill="#9ca3af"/>
                        <rect x="6" y="14" width="12" height="6" rx="3" stroke="currentColor" stroke-width="2" fill="#d1d5db"/>
                    </svg>
                    <span class="font-bold text-lg text-brand-navy">User</span>
                    <span class="text-xs text-brand-navy/60 mt-1">Book rides and travel</span>
                </button>
                <button onclick="window.location.href='/register/driver'" class="flex flex-col items-center border-2 border-brand-border rounded-2xl px-8 py-6 focus:outline-none shadow-sm hover:shadow-md hover:scale-105 transition-all bg-white">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mb-3 text-brand-amber" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <rect x="3" y="11" width="18" height="7" rx="3.5" stroke="#E06810" stroke-width="2" fill="#fbd5b3"/>
                        <circle cx="7.5" cy="18" r="2" fill="#E06810"/>
                        <circle cx="16.5" cy="18" r="2" fill="#E06810"/>
                        <rect x="7" y="7" width="10" height="4" rx="2" fill="#fce4cc"/>
                    </svg>
                    <span class="font-bold text-lg text-brand-navy">Driver</span>
                    <span class="text-xs text-brand-navy/60 mt-1">Offer rides and earn</span>
                </button>
            </div>
        </div>
        <div class="mt-2 text-center text-sm text-brand-navy/70">
            Already have an account? <a href="/login" class="text-brand-amber hover:text-brand-amber-600 hover:underline">Sign in</a>
        </div>
    </div>
</div>
@endsection
