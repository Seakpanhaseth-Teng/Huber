@extends('layouts.app')

@section('title', 'Change Password - Huber')

@section('content')
<div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8 mt-8">
    <div class="bg-white rounded-2xl border border-brand-border overflow-hidden">
        <div class="bg-brand-navy text-white px-6 py-4">
            <h3 class="text-lg font-semibold">
                <i class="fas fa-lock mr-2"></i>Change Password
            </h3>
        </div>
        <div class="p-6">
            <x-flash-message type="success" :dismissible="true" />
            <x-flash-message type="error" :dismissible="true" />

            <form method="POST" action="{{ route('password.change.submit') }}" id="passwordForm">
                @csrf
                @method('PUT')
                
                <div class="mb-4">
                    <label for="current_password" class="block text-brand-navy font-medium mb-1">
                        <i class="fas fa-key mr-1"></i>Current Password
                    </label>
                    <input type="password" class="w-full px-4 py-3 border border-brand-border rounded-brand text-brand-navy focus:border-brand-amber focus:ring-2 focus:ring-brand-amber/20 outline-none transition @error('current_password') border-red-500 @enderror" 
                           id="current_password" name="current_password" required>
                    @error('current_password')
                        <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="new_password" class="block text-brand-navy font-medium mb-1">
                        <i class="fas fa-lock mr-1"></i>New Password
                    </label>
                    <input type="password" class="w-full px-4 py-3 border border-brand-border rounded-brand text-brand-navy focus:border-brand-amber focus:ring-2 focus:ring-brand-amber/20 outline-none transition @error('new_password') border-red-500 @enderror" 
                           id="new_password" name="new_password" required>
                    @error('new_password')
                        <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                    @enderror
                    <div class="text-sm text-brand-navy/60 mt-1">Password must be at least 8 characters long.</div>
                </div>

                <div class="mb-6">
                    <label for="new_password_confirmation" class="block text-brand-navy font-medium mb-1">
                        <i class="fas fa-lock mr-1"></i>Confirm New Password
                    </label>
                    <input type="password" class="w-full px-4 py-3 border border-brand-border rounded-brand text-brand-navy focus:border-brand-amber focus:ring-2 focus:ring-brand-amber/20 outline-none transition @error('new_password_confirmation') border-red-500 @enderror" 
                           id="new_password_confirmation" name="new_password_confirmation" required>
                    @error('new_password_confirmation')
                        <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="flex items-center justify-between">
                    <a href="{{ route('user.profile') }}" class="inline-flex items-center gap-2 border border-brand-border text-brand-navy px-6 py-3 rounded-brand hover:bg-brand-amber-light/50 transition font-medium">
                        <i class="fas fa-arrow-left mr-2"></i>Back to Profile
                    </a>
                    <button type="submit" class="inline-flex items-center gap-2 bg-brand-amber hover:bg-brand-amber-600 text-white font-semibold px-6 py-3 rounded-brand transition">
                        <i class="fas fa-save mr-2"></i>Update Password
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
