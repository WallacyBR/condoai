<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- PWA Meta Tags -->
    <meta name="theme-color" content="#1e293b">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="CondoAI">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="application-name" content="CondoAI">
    <meta name="msapplication-TileColor" content="#1e293b">
    <meta name="msapplication-tap-highlight" content="no">
    
    <!-- PWA Icons -->
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon.ico">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon.ico">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">
    <link rel="mask-icon" href="/masked-icon.svg" color="#1e293b">
    
    <!-- PWA Manifest -->
    <link rel="manifest" href="/build/manifest.webmanifest">
    
    <title>CondoAI - Gestão de Condomínios</title>
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
