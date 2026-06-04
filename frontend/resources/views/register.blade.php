@extends('layouts.app')

@section('title', 'Join Huber - Register')

@section('content')
<div class="min-h-screen bg-brand-warm flex items-center justify-center py-12 px-4">
    <div class="bg-white rounded-2xl shadow-sm border border-brand-border p-8 w-full max-w-md mx-auto">
        <div class="flex flex-col items-center mb-6">
            <div class="bg-brand-amber-light/30 p-3 rounded-full mb-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-brand-amber" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 15c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
            </div>
            <h2 class="text-2xl font-bold text-brand-navy">Join Huber</h2>
            <p class="text-brand-navy/60 text-sm mt-1">Create your account and start your journey</p>
            <div class="flex items-center mt-4 gap-2">
                <span class="w-4 h-4 bg-brand-amber rounded-full flex items-center justify-center text-white text-xs">&#10003;</span>
                <span class="w-4 h-4 bg-brand-amber rounded-full flex items-center justify-center text-white text-xs">2</span>
                <span class="w-4 h-4 bg-brand-border rounded-full flex items-center justify-center text-brand-navy/40 text-xs">3</span>
            </div>
        </div>
        <form id="registerForm">
            <div class="grid grid-cols-2 gap-3 mb-4">
                <input type="text" id="first_name" name="first_name" placeholder="First Name" class="w-full px-4 py-3 border border-brand-border rounded-brand text-brand-navy placeholder:text-brand-navy/40 focus:border-brand-amber focus:ring-2 focus:ring-brand-amber/20 outline-none transition" required>
                <input type="text" id="last_name" name="last_name" placeholder="Last Name" class="w-full px-4 py-3 border border-brand-border rounded-brand text-brand-navy placeholder:text-brand-navy/40 focus:border-brand-amber focus:ring-2 focus:ring-brand-amber/20 outline-none transition" required>
            </div>
            <div class="mb-4">
                <input type="email" id="email" name="email" placeholder="Email Address" class="w-full px-4 py-3 border border-brand-border rounded-brand text-brand-navy placeholder:text-brand-navy/40 focus:border-brand-amber focus:ring-2 focus:ring-brand-amber/20 outline-none transition" required>
            </div>
            <div class="mb-4">
                <input type="text" id="phone" name="phone" placeholder="Phone Number" class="w-full px-4 py-3 border border-brand-border rounded-brand text-brand-navy placeholder:text-brand-navy/40 focus:border-brand-amber focus:ring-2 focus:ring-brand-amber/20 outline-none transition" required>
            </div>
            <div class="mb-4">
                <input type="password" id="password" name="password" placeholder="Password" class="w-full px-4 py-3 border border-brand-border rounded-brand text-brand-navy placeholder:text-brand-navy/40 focus:border-brand-amber focus:ring-2 focus:ring-brand-amber/20 outline-none transition" required>
            </div>
            <div class="mb-6">
                <input type="password" id="password_confirmation" name="password_confirmation" placeholder="Confirm Password" class="w-full px-4 py-3 border border-brand-border rounded-brand text-brand-navy placeholder:text-brand-navy/40 focus:border-brand-amber focus:ring-2 focus:ring-brand-amber/20 outline-none transition" required>
            </div>
            <div class="flex justify-between items-center mb-4">
                <button type="button" onclick="window.history.back()" class="border border-brand-border text-brand-navy px-6 py-3 rounded-brand hover:bg-brand-amber-light/50 transition">Back</button>
                <button type="submit" class="bg-brand-amber hover:bg-brand-amber-600 text-white font-semibold py-3 px-6 rounded-brand transition">Continue</button>
            </div>
            <div id="registerError" class="bg-red-50 text-red-700 p-3 rounded-lg mb-4 mt-2 hidden"></div>
        </form>
        <div class="mt-2 text-center text-sm text-brand-navy/70">
            Already have an account? <a href="/login" class="text-brand-amber hover:text-brand-amber-600 hover:underline">Sign in</a>
        </div>
    </div>
</div>
<script>
document.getElementById('registerForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const first_name = document.getElementById('first_name').value;
    const last_name = document.getElementById('last_name').value;
    const email = document.getElementById('email').value;
    const phone = document.getElementById('phone').value;
    const password = document.getElementById('password').value;
    const password_confirmation = document.getElementById('password_confirmation').value;
    const errorDiv = document.getElementById('registerError');
    errorDiv.classList.add('hidden');
    errorDiv.textContent = '';
    try {
        const response = await fetch('/api/register', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ first_name, last_name, email, phone, password, password_confirmation })
        });
        const data = await response.json();
        if (data.success) {
            window.location.href = '/login';
        } else {
            errorDiv.textContent = data.message || 'Registration failed.';
            errorDiv.classList.remove('hidden');
        }
    } catch (err) {
        errorDiv.textContent = 'An error occurred. Please try again.';
        errorDiv.classList.remove('hidden');
    }
});
</script>
@endsection
