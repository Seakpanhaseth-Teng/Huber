@extends('layouts.app')
@section('title', 'Huber - Home Page')

@section('content')
<!-- Hero Section -->
<header id="home" class="bg-gradient-to-br from-brand-amber to-brand-navy min-h-screen flex items-center">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center min-h-screen">
            <div>
                <h1 class="text-4xl md:text-5xl lg:text-6xl text-white font-bold mb-6 leading-tight">
                    Your Journey, Our Priority
                </h1>
                <p class="text-lg md:text-xl text-white/90 mb-8">
                    Experience the future of ride-sharing with Huber. Enjoy
                    seamless travel, competitive prices, and a trusted community
                    of drivers at your fingertips.
                </p>
                <div class="flex flex-wrap gap-4">
                    <a href="{{ route('register') }}" class="bg-white text-brand-amber font-semibold px-8 py-4 rounded-brand hover:bg-brand-amber-50 transition inline-block">Get Started</a>
                    <a href="#how-it-works" class="border-2 border-white text-white font-semibold px-8 py-4 rounded-brand hover:bg-white/10 transition inline-block">Learn More</a>
                </div>
                <div class="grid grid-cols-3 gap-6 mt-12 text-white">
                    <div class="text-center">
                        <h3 class="text-3xl font-bold">10K+</h3>
                        <p class="text-white/80">Active Users</p>
                    </div>
                    <div class="text-center">
                        <h3 class="text-3xl font-bold">5K+</h3>
                        <p class="text-white/80">Drivers</p>
                    </div>
                    <div class="text-center">
                        <h3 class="text-3xl font-bold">100K+</h3>
                        <p class="text-white/80">Rides</p>
                    </div>
                </div>
            </div>
            <div class="hidden lg:block">
                <img src="https://images.unsplash.com/photo-1449965408869-eaa3f722e40d?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80"
                    alt="Huber Car" class="w-full h-auto rounded-2xl shadow-2xl" />
            </div>
        </div>
    </div>
</header>

<!-- How It Works Section -->
<section id="how-it-works" class="py-16 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl md:text-4xl font-bold text-brand-navy mb-4">How Huber Works</h2>
            <p class="text-lg text-brand-navy/60 max-w-2xl mx-auto">
                Get started with Huber in three simple steps
            </p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="bg-white rounded-2xl border border-brand-border p-8 text-center hover:shadow-lg transition-shadow">
                <div class="w-20 h-20 bg-brand-amber-light/30 rounded-full flex items-center justify-center mx-auto mb-6">
                    <i class="fas fa-user-plus text-3xl text-brand-amber"></i>
                </div>
                <h3 class="text-xl font-bold text-brand-navy mb-3">Create Account</h3>
                <p class="text-brand-navy/60">
                    Sign up as a passenger or driver in minutes. Join our
                    growing community of trusted users.
                </p>
            </div>
            <div class="bg-white rounded-2xl border border-brand-border p-8 text-center hover:shadow-lg transition-shadow">
                <div class="w-20 h-20 bg-brand-amber-light/30 rounded-full flex items-center justify-center mx-auto mb-6">
                    <i class="fas fa-map-marker-alt text-3xl text-brand-amber"></i>
                </div>
                <h3 class="text-xl font-bold text-brand-navy mb-3">Book or List Ride</h3>
                <p class="text-brand-navy/60">
                    Find available rides or create listings as a driver. Set
                    your own schedule and preferences.
                </p>
            </div>
            <div class="bg-white rounded-2xl border border-brand-border p-8 text-center hover:shadow-lg transition-shadow">
                <div class="w-20 h-20 bg-brand-amber-light/30 rounded-full flex items-center justify-center mx-auto mb-6">
                    <i class="fas fa-star text-3xl text-brand-amber"></i>
                </div>
                <h3 class="text-xl font-bold text-brand-navy mb-3">Enjoy & Rate</h3>
                <p class="text-brand-navy/60">
                    Travel safely and share your experience. Help build a
                    trusted community through reviews.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section id="features" class="py-16 bg-brand-warm">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl md:text-4xl font-bold text-brand-navy mb-4">Why Choose Huber?</h2>
            <p class="text-lg text-brand-navy/60 max-w-2xl mx-auto">
                Experience the best in modern ride-sharing
            </p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="bg-white rounded-2xl border border-brand-border p-6 text-center hover:shadow-lg transition-shadow">
                <i class="fas fa-shield-alt text-3xl text-brand-amber mb-4"></i>
                <h4 class="text-lg font-bold text-brand-navy mb-2">Safe & Secure</h4>
                <p class="text-brand-navy/60 text-sm">
                    Verified drivers and secure payment system for
                    worry-free travel.
                </p>
            </div>
            <div class="bg-white rounded-2xl border border-brand-border p-6 text-center hover:shadow-lg transition-shadow">
                <i class="fas fa-dollar-sign text-3xl text-brand-amber mb-4"></i>
                <h4 class="text-lg font-bold text-brand-navy mb-2">Best Prices</h4>
                <p class="text-brand-navy/60 text-sm">
                    Competitive rates and transparent pricing with no hidden
                    fees.
                </p>
            </div>
            <div class="bg-white rounded-2xl border border-brand-border p-6 text-center hover:shadow-lg transition-shadow">
                <i class="fas fa-clock text-3xl text-brand-amber mb-4"></i>
                <h4 class="text-lg font-bold text-brand-navy mb-2">24/7 Support</h4>
                <p class="text-brand-navy/60 text-sm">
                    Round-the-clock customer assistance whenever you need
                    help.
                </p>
            </div>
            <div class="bg-white rounded-2xl border border-brand-border p-6 text-center hover:shadow-lg transition-shadow">
                <i class="fas fa-mobile-alt text-3xl text-brand-amber mb-4"></i>
                <h4 class="text-lg font-bold text-brand-navy mb-2">Easy to Use</h4>
                <p class="text-brand-navy/60 text-sm">
                    User-friendly interface for seamless booking and ride
                    management.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Testimonials Section -->
<section id="testimonials" class="py-16 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl md:text-4xl font-bold text-brand-navy mb-4">What Our Users Say</h2>
            <p class="text-lg text-brand-navy/60 max-w-2xl mx-auto">Real experiences from our community</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-brand-warm rounded-2xl border border-brand-border p-6">
                <div class="mb-4">
                    <i class="fas fa-quote-left text-brand-amber/40 text-2xl mb-3"></i>
                    <p class="text-brand-navy/80">
                        "Huber has made my daily commute so much easier.
                        The drivers are professional and the app is super
                        easy to use!"
                    </p>
                </div>
                <div>
                    <h5 class="font-bold text-brand-navy">Sarah M.</h5>
                    <p class="text-brand-navy/60 text-sm">Regular Passenger</p>
                </div>
            </div>
            <div class="bg-brand-warm rounded-2xl border border-brand-border p-6">
                <div class="mb-4">
                    <i class="fas fa-quote-left text-brand-amber/40 text-2xl mb-3"></i>
                    <p class="text-brand-navy/80">
                        "As a driver, I love the flexibility Huber offers.
                        I can set my own schedule and earn extra income on
                        my terms."
                    </p>
                </div>
                <div>
                    <h5 class="font-bold text-brand-navy">John D.</h5>
                    <p class="text-brand-navy/60 text-sm">Driver Partner</p>
                </div>
            </div>
            <div class="bg-brand-warm rounded-2xl border border-brand-border p-6">
                <div class="mb-4">
                    <i class="fas fa-quote-left text-brand-amber/40 text-2xl mb-3"></i>
                    <p class="text-brand-navy/80">
                        "The best ride-sharing platform I've used. Great
                        prices, friendly drivers, and excellent customer
                        support!"
                    </p>
                </div>
                <div>
                    <h5 class="font-bold text-brand-navy">Mike R.</h5>
                    <p class="text-brand-navy/60 text-sm">Business Traveler</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- FAQ Section -->
<section id="faq" class="py-16 bg-brand-warm">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl md:text-4xl font-bold text-brand-navy mb-4">Frequently Asked Questions</h2>
            <p class="text-lg text-brand-navy/60 max-w-2xl mx-auto">
                Find answers to common questions about Huber
            </p>
        </div>
        <div class="max-w-3xl mx-auto">
            <div class="space-y-0" id="faqAccordion">
                <div class="border-b border-brand-border py-4">
                    <button class="w-full flex items-center justify-between py-4 text-brand-navy font-medium text-left" type="button" data-toggle="accordion" data-target="#faq1">
                        How do I become a driver?
                        <i class="fas fa-chevron-down text-brand-amber transition-transform"></i>
                    </button>
                    <div id="faq1" class="accordion-collapse show">
                        <div class="pb-4 text-brand-navy/70">
                            To become a driver, simply sign up through our
                            app or website, submit required documentation,
                            and complete our verification process. Once
                            approved, you can start accepting rides!
                        </div>
                    </div>
                </div>
                <div class="border-b border-brand-border py-4">
                    <button class="w-full flex items-center justify-between py-4 text-brand-navy font-medium text-left" type="button" data-toggle="accordion" data-target="#faq2">
                        How is the fare calculated?
                        <i class="fas fa-chevron-down text-brand-amber transition-transform"></i>
                    </button>
                    <div id="faq2" class="accordion-collapse collapse">
                        <div class="pb-4 text-brand-navy/70">
                            Fares are calculated based on distance, time,
                            and current demand. All prices are shown upfront
                            before you confirm your ride.
                        </div>
                    </div>
                </div>
                <div class="border-b border-brand-border py-4">
                    <button class="w-full flex items-center justify-between py-4 text-brand-navy font-medium text-left" type="button" data-toggle="accordion" data-target="#faq3">
                        Is my payment information secure?
                        <i class="fas fa-chevron-down text-brand-amber transition-transform"></i>
                    </button>
                    <div id="faq3" class="accordion-collapse collapse">
                        <div class="pb-4 text-brand-navy/70">
                            Yes, we use industry-standard encryption to
                            protect your payment information. All
                            transactions are processed securely through our
                            trusted payment partners.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
