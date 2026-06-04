<nav class="bg-brand-navy text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
            <!-- Brand -->
            <a class="flex items-center gap-2 text-white no-underline font-bold text-xl hover:text-brand-amber transition-colors" href="{{ route('home') }}">
                <i class="fas fa-car-side text-brand-amber"></i> Huber
            </a>

            <!-- Mobile Toggler -->
            <button type="button" class="lg:hidden text-white hover:text-brand-amber focus:outline-none" data-toggle="navbar" aria-expanded="false" aria-label="Toggle navigation">
                <i class="fas fa-bars text-2xl"></i>
            </button>

            <!-- Nav Links -->
            <div class="navbar-collapse hidden lg:flex lg:items-center lg:gap-2" id="navbarNav">
                <ul class="flex flex-col lg:flex-row lg:items-center lg:gap-1 list-none ml-auto mb-0">
                    @auth
                        @if(auth()->user()->role === 'driver')
                            <li>
                                <a class="block px-4 py-2 text-white no-underline hover:text-brand-amber hover:bg-brand-navy-700 rounded-lg transition-colors" href="{{ route('driver.ride.management') }}">
                                    <i class="fas fa-tasks mr-2"></i>Ride Management
                                </a>
                            </li>
                        @endif
                        <li>
                            <a class="block px-4 py-2 text-white no-underline hover:text-brand-amber hover:bg-brand-navy-700 rounded-lg transition-colors" href="{{ route('find.rides') }}">
                                <i class="fas fa-search mr-2"></i>Find Rides
                            </a>
                        </li>
                        <li>
                            <a class="block px-4 py-2 text-white no-underline hover:text-brand-amber hover:bg-brand-navy-700 rounded-lg transition-colors" href="{{ route('user.bookings') }}">
                                <i class="fas fa-list mr-2"></i>My Bookings
                            </a>
                        </li>
                        <li class="relative">
                            <a class="block px-4 py-2 text-white no-underline hover:text-brand-amber hover:bg-brand-navy-700 rounded-lg transition-colors cursor-pointer" data-toggle="dropdown" href="#" id="navbarDropdown">
                                <i class="fas fa-user-circle mr-2"></i>{{ auth()->user()->name }} <i class="fas fa-chevron-down text-xs ml-1"></i>
                            </a>
                            <ul class="dropdown-menu absolute right-0 mt-1 bg-white rounded-xl shadow-lg border border-brand-border min-w-[200px] hidden z-50 overflow-hidden">
                                <li>
                                    <a class="block px-4 py-3 text-brand-navy no-underline hover:bg-brand-amber-light/50 transition-colors" href="{{ route('user.profile') }}">
                                        <i class="fas fa-user mr-2 text-brand-amber"></i>Profile Management
                                    </a>
                                </li>
                                <li>
                                    <form method="POST" action="{{ route('logout') }}" class="m-0">
                                        @csrf
                                        <button type="submit" class="w-full text-left px-4 py-3 text-brand-navy hover:bg-red-50 transition-colors cursor-pointer border-0 bg-transparent">
                                            <i class="fas fa-sign-out-alt mr-2 text-red-500"></i>Logout
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </li>
                    @else
                        <li>
                            <a class="block px-4 py-2 text-white/80 no-underline hover:text-white transition-colors" href="{{ route('login') }}">Login</a>
                        </li>
                        <li>
                            <a class="block px-4 py-2 ml-2 bg-brand-amber text-white no-underline rounded-brand font-semibold hover:bg-brand-amber-600 transition-colors" href="{{ route('register') }}">Register</a>
                        </li>
                    @endauth
                </ul>
            </div>
        </div>
    </div>
</nav>