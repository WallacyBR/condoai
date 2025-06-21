<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use Illuminate\Support\Facades\Route;

// Rota para página offline
Route::get('/offline', function () {
    return view('offline');
})->name('offline');

// Rota principal - exibe apenas o LoginView (excluindo rotas da API)
Route::get('/{any}', function () {
    return view('app');
})->where('any', '^(?!api).*$');

// Routes de autenticação
Route::post('login', [LoginController::class, 'login'])->name('login');
Route::post('register', [RegisterController::class, 'register'])->name('register');
Route::post('logout', [LoginController::class, 'logout'])->name('logout');
