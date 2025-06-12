<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name', 'CondoAI'))</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- AlpineJS -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="min-h-screen bg-background font-sans antialiased">
    <div class="min-h-screen flex flex-col">


        <!-- SPA Mount Point -->
    <div id="app"></div>
    
    <!-- Adicionar meta tags para dados do usuário -->
    <meta name="user-authenticated" content="{{ auth()->check() ? 'true' : 'false' }}">
    @auth
        <script type="application/json" data-user>
            {!! json_encode([
                'id' => auth()->user()->id,
                'name' => auth()->user()->name,
                'email' => auth()->user()->email
            ]) !!}
        </script>
    @endauth


    </div>
</body>
</html>
