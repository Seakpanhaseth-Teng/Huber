@extends('layouts.app')
@section('title', 'Verification Pending')
@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8 mt-8">
    <div class="bg-white rounded-2xl border border-brand-border overflow-hidden">
        <div class="bg-amber-400 text-brand-navy text-center px-6 py-6">
            <i class="fas fa-clock fa-3x mb-3"></i>
            <h2 class="text-2xl font-bold mb-0">Verification Pending</h2>
        </div>
        <div class="p-8 text-center">
            <div class="mb-4">
                <i class="fas fa-file-alt text-brand-amber" style="font-size: 4rem;"></i>
            </div>
            
            <h3 class="text-xl font-bold text-brand-amber mb-4">Thank you for submitting your documents!</h3>
            
            <div class="flex items-center gap-3 border border-blue-300 bg-blue-50 text-blue-700 rounded-lg px-6 py-4 text-left mb-6">
                <div>
                    <p class="mb-2 font-semibold">Your driver application is currently under review.</p>
                    <p class="mb-0">Our team at Huber is carefully reviewing your submitted documents to ensure everything meets our safety and compliance standards.</p>
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6 mb-6">
                <div class="text-center">
                    <i class="fas fa-search text-blue-500 fa-2x mb-2"></i>
                    <h5 class="font-semibold text-brand-navy">Document Review</h5>
                    <p class="text-brand-navy/60 text-sm">We're reviewing your driver's license, vehicle registration, and insurance documents.</p>
                </div>
                <div class="text-center">
                    <i class="fas fa-shield-alt text-green-500 fa-2x mb-2"></i>
                    <h5 class="font-semibold text-brand-navy">Safety Check</h5>
                    <p class="text-brand-navy/60 text-sm">Ensuring your vehicle meets our safety requirements and standards.</p>
                </div>
                <div class="text-center">
                    <i class="fas fa-check-circle text-brand-amber fa-2x mb-2"></i>
                    <h5 class="font-semibold text-brand-navy">Final Approval</h5>
                    <p class="text-brand-navy/60 text-sm">Once approved, you'll be able to start accepting rides immediately.</p>
                </div>
            </div>
            
            <hr class="my-6 border-brand-border">
            
            <div class="bg-brand-warm border border-brand-border rounded-lg px-6 py-4 text-left mb-6">
                <h6 class="font-semibold text-brand-navy mb-3">
                    <i class="fas fa-info-circle mr-2"></i>What happens next?
                </h6>
                <ul class="list-disc list-inside text-brand-navy/70 space-y-1 text-sm">
                    <li>You'll receive an email notification once your verification is complete</li>
                    <li>This process typically takes 24-48 hours</li>
                    <li>You can log in anytime to check your status</li>
                    <li>If there are any issues, we'll contact you directly</li>
                </ul>
            </div>
            
            <div class="flex items-center justify-center gap-4 mt-6">
                <a href="{{ route('logout') }}" class="inline-flex items-center gap-2 border border-brand-border text-brand-navy px-6 py-3 rounded-brand hover:bg-brand-amber-light/50 transition font-medium">
                    <i class="fas fa-sign-out-alt mr-1"></i>Logout
                </a>
                <button onclick="location.reload()" class="inline-flex items-center gap-2 bg-brand-amber hover:bg-brand-amber-600 text-white font-semibold px-6 py-3 rounded-brand transition">
                    <i class="fas fa-refresh mr-1"></i>Check Status
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
