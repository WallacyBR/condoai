<?php

use Illuminate\Support\Facades\Route;
use App\Helpers\AssetHelper;

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;

// Routes de API para autenticação
Route::post('login', [LoginController::class, 'login'])->name('login');
Route::post('register', [RegisterController::class, 'register'])->name('register');
Route::post('logout', [LoginController::class, 'logout'])->name('logout');

// Rota principal - serve o SPA sem Blade
Route::get('/{any}', function () {
    $authenticated = auth()->check() ? 'true' : 'false';
    $userScript = '';
    if (auth()->check()) {
        $userData = json_encode([
            'id' => auth()->id(),
            'name' => auth()->user()->name,
            'email' => auth()->user()->email,
        ]);
        $userScript = "<script type=\"application/json\" data-user>{$userData}</script>";
    }

    $css = AssetHelper::asset('resources/css/app.css');
    $js = AssetHelper::asset('resources/js/app.js');


    return <<<HTML
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>CondoAI</title>
    <link rel="stylesheet" href="{$css}">
    <script type="module" src="{$js}"></script>
</head>
<body class="min-h-screen bg-background font-sans antialiased">
    <div id="app"></div>
    <meta name="user-authenticated" content="{$authenticated}">
    {$userScript}
</body>
</html>
HTML;
})->where('any', '.*');
