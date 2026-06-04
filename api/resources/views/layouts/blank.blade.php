<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Huber API')</title>
    @vite(['resources/css/app.css'])
    @yield('head')
</head>
<body class="bg-brand-warm text-brand-navy min-h-screen">
    @yield('content')
    @vite(['resources/js/app.js'])
    @yield('scripts')
</body>
</html>