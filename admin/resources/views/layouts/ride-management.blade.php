<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Ride Management - Huber')</title>
    @vite(['resources/css/app.css'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" integrity="sha512-9usAa10IRO0HhonpyAIVpjrylPvoDwiPUiKdWk5t3PyolY1cOd4DSE0Ga+ri4AuTroPR5aQvXU9xC6qOPnzFeg==" crossorigin="anonymous">
    @yield('style')
</head>
<body class="bg-brand-warm text-brand-navy min-h-screen">
    @include('layouts.partials.navbar')
    <div class="flex max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 gap-6 py-6">
        <nav class="w-64 shrink-0 bg-white rounded-2xl border border-brand-border py-4 shadow-sm hidden lg:block" style="min-height: calc(100vh - 8rem);">
            @include('ride-management.sidebar')
        </nav>
        <main class="flex-1 min-w-0">
            @yield('main')
        </main>
    </div>
    @include('layouts.partials.footer')
    @vite(['resources/js/app.js'])
    @yield('scripts')
</body>
</html> 