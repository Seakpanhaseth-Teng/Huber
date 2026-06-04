<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Dashboard')</title>
    @vite(['resources/css/app.css'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-51n8xEQG7E5szF60R3A69f5P1Q8kBp/WmDZ3jF36w3C1amYLO2o1Mvx8MwO7lH4e2QTh0kTEHnYJ4X2CYbH4w==" crossorigin="anonymous">
    @yield('styles')
</head>
<body class="bg-brand-warm text-brand-navy min-h-screen">
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <nav class="w-56 fixed top-0 left-0 bottom-0 z-50 bg-white border-r border-brand-border flex flex-col p-6 shadow-sm">
            <div class="text-brand-amber text-xl font-bold text-center mb-8 tracking-wider">
                <i class="fas fa-car mr-2"></i> Huber
            </div>
            <div class="flex-1">
                <div class="text-xs text-gray-400 uppercase tracking-wider mb-2 mt-4">Users</div>
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg mb-1.5 font-medium transition-colors no-underline {{ request()->routeIs('admin.dashboard') ? 'bg-brand-amber text-white font-bold' : 'text-brand-navy hover:bg-brand-amber-light/50 hover:text-brand-amber' }}">
                    <i class="fas fa-tachometer-alt text-lg"></i> <span>Dashboard</span>
                </a>
                <a href="{{ route('admin.users.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg mb-1.5 font-medium transition-colors no-underline {{ request()->routeIs('admin.users.*') ? 'bg-brand-amber text-white font-bold' : 'text-brand-navy hover:bg-brand-amber-light/50 hover:text-brand-amber' }}">
                    <i class="fas fa-users text-lg"></i> <span>Users</span>
                </a>
                <div class="text-xs text-gray-400 uppercase tracking-wider mb-2 mt-4">Drivers</div>
                <a href="{{ route('admin.drivers.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg mb-1.5 font-medium transition-colors no-underline {{ request()->routeIs('admin.drivers.*') ? 'bg-brand-amber text-white font-bold' : 'text-brand-navy hover:bg-brand-amber-light/50 hover:text-brand-amber' }}">
                    <i class="fas fa-user-tie text-lg"></i> <span>Drivers</span>
                </a>
                <a href="{{ route('admin.rides.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg mb-1.5 font-medium transition-colors no-underline {{ request()->routeIs('admin.rides.*') ? 'bg-brand-amber text-white font-bold' : 'text-brand-navy hover:bg-brand-amber-light/50 hover:text-brand-amber' }}">
                    <i class="fas fa-car text-lg"></i> <span>Rides</span>
                </a>
                <a href="{{ route('admin.driver_documents.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg mb-1.5 font-medium transition-colors no-underline {{ request()->routeIs('admin.driver_documents.*') ? 'bg-brand-amber text-white font-bold' : 'text-brand-navy hover:bg-brand-amber-light/50 hover:text-brand-amber' }}">
                    <i class="fas fa-file-alt text-lg"></i> <span>Driver Documents</span>
                </a>
            </div>
            <form action="{{ route('admin.logout') }}" method="POST" class="mt-6 text-center">
                @csrf
                <button type="submit" class="w-full bg-white text-red-500 border border-red-400 px-4 py-2 rounded-brand font-semibold transition-colors hover:bg-red-500 hover:text-white cursor-pointer">
                    <i class="fas fa-sign-out-alt mr-1"></i> Logout
                </button>
            </form>
        </nav>
        <!-- Main Content -->
        <main class="flex-1 ml-56 p-8 min-h-screen bg-white">
            <div class="text-center mb-6 pb-4 border-b border-gray-100">
                <h1 class="text-brand-navy font-bold text-3xl mb-1">@yield('title', 'Admin Dashboard')</h1>
                <p class="text-gray-500 text-lg">@yield('subtitle', 'Manage your application with ease')</p>
            </div>
            @if(session('success'))
                <div class="bg-green-600 text-white px-4 py-3 rounded-lg mb-6 text-center font-medium">
                    <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="bg-red-500 text-white px-4 py-3 rounded-lg mb-6 text-center font-medium">
                    <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
                </div>
            @endif
            @if($errors->any())
                <div class="bg-red-500 text-white px-4 py-3 rounded-lg mb-6 text-center font-medium">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    @foreach($errors->all() as $error)
                        {{ $error }}<br>
                    @endforeach
                </div>
            @endif
            @yield('content')
        </main>
    </div>
    @vite(['resources/js/app.js'])
    @yield('scripts')
</body>
</html>