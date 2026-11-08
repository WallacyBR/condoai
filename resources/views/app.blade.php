<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>CondoAI</title>
    @vite(["resources/css/app.css", "resources/js/app.js"])
</head>
<body class="min-h-screen bg-background font-sans antialiased">
    <div id="app"></div>
    <meta name="user-authenticated" content="{{ auth()->check() ? 'true' : 'false' }}">
    @auth
        <script type="application/json" data-user>
            {{ json_encode(['id' => auth()->id(), 'name' => auth()->user()->name, 'email' => auth()->user()->email]) }}
        </script>
    @endauth
</body>
</html>
