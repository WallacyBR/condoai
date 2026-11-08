<?php

use App\Http\Controllers\API\ApartmentController;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\BlockController;
use App\Http\Controllers\API\CondominiumController;
use App\Http\Controllers\API\ModuleController;
use App\Http\Controllers\API\ParkingSpotController;
use App\Http\Controllers\API\ProfileController;
use App\Http\Controllers\API\ResidentController;
use App\Http\Controllers\API\TowerController;
use App\Http\Controllers\API\UserController;
use Illuminate\Support\Facades\Route;

Route::controller(AuthController::class)->group(function () {
    Route::post('auth/login', 'login');
    Route::post('auth/register', 'register');
    Route::post('auth/logout', 'logout');
    Route::post('auth/refresh', 'refresh');
    Route::get('auth/me', 'me');
});

Route::middleware('auth:api')->group(function () {
    Route::apiResource('condominiums', CondominiumController::class);
    Route::apiResource('residents', ResidentController::class);
    Route::apiResource('towers', TowerController::class);
    Route::apiResource('blocks', BlockController::class);
    Route::apiResource('apartments', ApartmentController::class);
    Route::apiResource('profiles', ProfileController::class);
    Route::apiResource('modules', ModuleController::class);
    Route::apiResource('users', UserController::class);
    Route::apiResource('parking-spots', ParkingSpotController::class);

    Route::post('parking-spots/{parking_spot}/rent', [ParkingSpotController::class, 'rent']);

    // Rotas adicionais para gerenciar moradores de apartamentos
    Route::post('apartments/{apartment}/residents', [ApartmentController::class, 'addResident']);
    Route::delete('apartments/{apartment}/residents', [ApartmentController::class, 'removeResident']);
});
