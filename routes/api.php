<?php

use App\Http\Controllers\API\ApartmentController;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\BlockController;
use App\Http\Controllers\API\CondominiumController;
use App\Http\Controllers\API\HealthController;
use App\Http\Controllers\API\ImageUploadController;
use App\Http\Controllers\API\ModuleController;
use App\Http\Controllers\API\ParkingSpotController;
use App\Http\Controllers\API\VehicleController;
use App\Http\Controllers\API\CommonAreaController;
use App\Http\Controllers\API\ProfileController;
use App\Http\Controllers\API\ResidentController;
use App\Http\Controllers\API\TowerController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\Api\OccurrenceController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

// Health check endpoint
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now()->toISOString(),
        'version' => '1.0.0'
    ]);
});

// Rota de teste temporária para upload sem autenticação
Route::post('test-upload', function (Request $request) {
    return response()->json([
        'success' => true,
        'message' => 'Rota de teste funcionando',
        'data' => $request->all()
    ]);
});

Route::controller(AuthController::class)->group(function () {
    Route::post('auth/login', 'login');
    Route::post('auth/register', 'register');
    Route::post('auth/logout', 'logout');
    Route::post('auth/refresh', 'refresh');
    Route::get('auth/me', 'me');
});

Route::middleware(['auth:api', 'tenant'])->group(function () {
    Route::get('condominiums/{condominium}/stats', [CondominiumController::class, 'stats']);
    Route::apiResource('condominiums', CondominiumController::class);
    Route::apiResource('residents', ResidentController::class);
    Route::apiResource('towers', TowerController::class);
    Route::apiResource('blocks', BlockController::class);
    Route::apiResource('apartments', ApartmentController::class);
    Route::apiResource('profiles', ProfileController::class);
    Route::apiResource('modules', ModuleController::class);
    Route::apiResource('users', UserController::class);
    Route::apiResource('parking-spots', ParkingSpotController::class);
    Route::apiResource('vehicles', VehicleController::class);
    Route::apiResource('pets', \App\Http\Controllers\API\PetController::class);
    Route::apiResource('common-areas', CommonAreaController::class);
    Route::apiResource('packages', \App\Http\Controllers\API\PackageController::class);

    // Notice and Document Routes
    Route::apiResource('notices', \App\Http\Controllers\API\NoticeController::class);
    Route::get('documents', [\App\Http\Controllers\Api\DocumentController::class, 'index'])->name('documents.index');

    Route::get('common-areas/{common_area}/bookings', [CommonAreaController::class, 'bookings']);
    Route::post('common-areas/{common_area}/bookings', [CommonAreaController::class, 'book']);
    Route::patch('common-areas/{common_area}/bookings/{booking}/cancel', [CommonAreaController::class, 'cancelBooking']);
    Route::get('common-areas/{common_area}/available-slots', [CommonAreaController::class, 'getAvailableSlots']);

    Route::post('parking-spots/batch', [ParkingSpotController::class, 'batchStore']);
    Route::post('parking-spots/{parking_spot}/rent', [ParkingSpotController::class, 'rent']);
    Route::post('packages/{package}/withdraw', [\App\Http\Controllers\API\PackageController::class, 'withdraw']);

    // Rotas adicionais para gerenciar moradores de apartamentos
    Route::post('apartments/{apartment}/residents', [ApartmentController::class, 'addResident']);
    Route::delete('apartments/{apartment}/residents', [ApartmentController::class, 'removeResident']);

    // Rotas para upload de imagens
    Route::post('images/upload', [ImageUploadController::class, 'upload']);
    Route::delete('images/delete', [ImageUploadController::class, 'delete']);

    // Classifieds (Ads) Routes
    Route::apiResource('ads', \App\Http\Controllers\AdController::class);
    Route::patch('ads/{ad}/mark-sold', [\App\Http\Controllers\AdController::class, 'markAsSold']);
    Route::patch('ads/{ad}/mark-unsold', [\App\Http\Controllers\AdController::class, 'markAsUnsold']);
    Route::post('ads/{ad}/report', [\App\Http\Controllers\AdController::class, 'reportAd']);
    Route::post('ads/{ad}/contact-click', [\App\Http\Controllers\AdController::class, 'contactClick']);
    Route::get('my-ads', [\App\Http\Controllers\AdController::class, 'myAds']);
    Route::get('ads/{ad}/statistics', [\App\Http\Controllers\AdController::class, 'adStatistics']);

    // Occurrence Routes
    Route::apiResource('occurrences', OccurrenceController::class);
    Route::post('occurrences/{occurrence}/photos', [OccurrenceController::class, 'addPhoto']);
    Route::delete('occurrences/{occurrence}/photos/{photo}', [OccurrenceController::class, 'deletePhoto']);
    Route::post('occurrences/{occurrence}/comments', [OccurrenceController::class, 'addComment']);
    Route::delete('occurrences/{occurrence}/comments/{comment}', [OccurrenceController::class, 'deleteComment']);

    // Maintenance Module Routes
    Route::prefix('maintenance')->name('maintenance.')->group(function () {
        Route::apiResource('areas', \App\Http\Controllers\API\MaintenanceAreaController::class);
        Route::apiResource('equipments', \App\Http\Controllers\API\MaintenanceEquipmentController::class);
        Route::post('equipments/{equipment}/sync-qr-access-users', [\App\Http\Controllers\API\MaintenanceEquipmentController::class, 'syncQrAccessUsers'])
            ->name('equipments.syncQrAccessUsers');

        // Rota para tarefas do dia do usuário
        Route::get('schedules/my-today', [\App\Http\Controllers\API\MaintenanceScheduleController::class, 'myTodaySchedules'])
            ->name('schedules.myToday');
        // Rota para atualizar/verificar status das tarefas
        Route::post('schedules/refresh-today', [\App\Http\Controllers\API\MaintenanceScheduleController::class, 'refreshTodaySchedules'])
            ->name('schedules.refreshToday');
        // Rota para manutenções concluídas do usuário
        Route::get('schedules/my-completed', [\App\Http\Controllers\API\MaintenanceScheduleController::class, 'myCompletedRecords'])
            ->name('schedules.myCompleted');
        Route::apiResource('schedules', \App\Http\Controllers\API\MaintenanceScheduleController::class);

        Route::apiResource('records', \App\Http\Controllers\API\MaintenanceRecordController::class);
        Route::post('records/{record}/update-status', [\App\Http\Controllers\API\MaintenanceRecordController::class, 'updateStatus'])
            ->name('records.updateStatus');

        // Dashboard Routes
        Route::get('dashboard/general-stats', [\App\Http\Controllers\API\MaintenanceDashboardController::class, 'getGeneralStats'])
            ->name('dashboard.generalStats');
        Route::get('dashboard/by-equipment', [\App\Http\Controllers\API\MaintenanceDashboardController::class, 'getStatsByEquipment'])
            ->name('dashboard.byEquipment');
        Route::get('dashboard/by-area', [\App\Http\Controllers\API\MaintenanceDashboardController::class, 'getStatsByArea'])
            ->name('dashboard.byArea');
    });
});

// Public QR Code Report Route
Route::get('/qr/{qr_code_hash}', [\App\Http\Controllers\API\PublicMaintenanceReportController::class, 'showReportByQrCode'])
    ->name('public.maintenance.report');
