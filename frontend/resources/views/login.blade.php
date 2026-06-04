@extends('layouts.app')
@section('title', 'Login - Huber')

@section('content')
<div class="min-h-[80vh] flex items-center justify-center px-4 py-12">
    <div class="bg-white p-8 rounded-2xl shadow-sm border border-brand-border w-full max-w-md">
        <div class="flex flex-col items-center mb-6">
            <div class="bg-brand-amber-light/50 p-3 rounded-full mb-3">
                <i class="fas fa-user text-2xl text-brand-amber"></i>
            </div>
            <h2 class="text-2xl font-bold text-brand-navy">Login to Huber</h2>
            <p class="text-muted text-sm mt-1">Sign in to your account</p>
        </div>

        @if(session('error'))
            <div class="flex items-center gap-2 border border-red-300 bg-red-50 text-red-700 rounded-lg px-4 py-3 mb-4">
                <i class="fas fa-exclamation-circle text-red-500"></i>
                <span>{{ session('error') }}</span>
                <button type="button" class="ml-auto text-red-500 hover:text-red-700 cursor-pointer bg-transparent border-0 text-xl leading-none" data-dismiss="alert">&times;</button>
            </div>
        @endif
        @if($errors->any())
            <div class="border border-red-300 bg-red-50 text-red-700 rounded-lg px-4 py-3 mb-4">
                <ul class="list-disc pl-5 m-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf
            <div class="mb-4">
                <label for="email" class="block text-brand-navy font-medium mb-1">Email Address</label>
                <input type="email" id="email" name="email" class="w-full px-4 py-3 border border-brand-border rounded-brand text-brand-navy focus:border-brand-amber focus:ring-2 focus:ring-brand-amber/20 outline-none transition-colors" required autofocus value="{{ old('email') }}">
            </div>
            <div class="mb-6">
                <label for="password" class="block text-brand-navy font-medium mb-1">Password</label>
                <input type="password" id="password" name="password" class="w-full px-4 py-3 border border-brand-border rounded-brand text-brand-navy focus:border-brand-amber focus:ring-2 focus:ring-brand-amber/20 outline-none transition-colors" required>
            </div>
            <button type="submit" class="w-full bg-brand-amber text-white font-semibold py-3 rounded-brand hover:bg-brand-amber-600 transition-colors cursor-pointer border-0">
                <i class="fas fa-sign-in-alt mr-2"></i>Login
            </button>
        </form>
        <div class="mt-6 text-center text-sm">
            <p class="text-muted mb-3">Don't have an account?</p>
            <a href="/register" class="inline-block bg-brand-amber-light text-brand-amber-dark font-semibold px-6 py-2.5 rounded-brand hover:bg-brand-amber-light/80 transition-colors no-underline">
                Register
            </a>
        </div>
        <div class="mt-4 text-center">
            <a href="/" class="text-brand-amber hover:underline no-underline text-sm">
                <i class="fas fa-arrow-left mr-1"></i>Back to Home
            </a>
        </div>
    </div>
</div>
@endsection