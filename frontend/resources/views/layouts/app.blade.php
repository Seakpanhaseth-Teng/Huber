<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Huber')</title>
    @vite(['resources/css/app.css'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" integrity="sha512-9usAa10IRO0HhonpyAIVpjrylPvoDwiPUiKdWk5t3PyolY1cOd4DSE0Ga+ri4AuTroPR5aQvXU9xC6qOPnzFeg==" crossorigin="anonymous">
    @yield('style')
</head>

<body class="bg-brand-warm text-brand-navy min-h-screen flex flex-col">
    <!-- Navbar -->
    @include('layouts.partials.navbar')

    <!-- Main Content -->
    <main class="flex-1">
        @if(session('success'))
            <div class="alert border border-brand-amber-light bg-brand-amber-light/20 text-brand-amber-dark rounded-lg px-4 py-3 mx-4 mt-4 flex items-center gap-2" role="alert">
                <i class="fas fa-check-circle text-brand-amber"></i>
                <span>{{ session('success') }}</span>
                <button type="button" class="ml-auto text-brand-navy hover:text-brand-amber cursor-pointer" data-dismiss="alert">&times;</button>
            </div>
        @endif
        @if($errors->any())
            <div class="alert border border-red-300 bg-red-50 text-red-700 rounded-lg px-4 py-3 mx-4 mt-4" role="alert">
                <i class="fas fa-exclamation-circle mr-2"></i>
                @foreach($errors->all() as $error)
                    {{ $error }}<br>
                @endforeach
            </div>
        @endif
        @yield('content')
    </main>

    <!-- Footer -->
    @include('layouts.partials.footer')

    @vite(['resources/js/app.js'])
    @yield('scripts')
</body>

</html>
