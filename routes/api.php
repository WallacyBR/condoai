<?php

use App\Http\Controllers\API\AccessControlDashboardController;
use App\Http\Controllers\API\AccessRequestController;
use App\Http\Controllers\API\ApartmentController;
use App\Http\Controllers\API\AssemblyController;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\BlockController;
use App\Http\Controllers\API\CommonAreaController;
use App\Http\Controllers\API\CondominiumController;
use App\Http\Controllers\API\ImageUploadController;
use App\Http\Controllers\API\ModuleController;
use App\Http\Controllers\API\NotificationInboxController;
use App\Http\Controllers\API\OccurrenceController;
use App\Http\Controllers\API\OperationalReportController;
use App\Http\Controllers\API\ParkingSpotController;
use App\Http\Controllers\API\PasskeyAuthController;
use App\Http\Controllers\API\ProcurementController;
use App\Http\Controllers\API\ProfileAnalyticsDashboardController;
use App\Http\Controllers\API\ProfileController;
use App\Http\Controllers\API\ProfileManagementController;
use App\Http\Controllers\API\ProfileSettingsController;
use App\Http\Controllers\API\ProtectiveMeasureController;
use App\Http\Controllers\API\PublicSupplierQuoteController;
use App\Http\Controllers\API\ResidentController;
use App\Http\Controllers\API\SecurityAdminController;
use App\Http\Controllers\API\SecurityPasskeyController;
use App\Http\Controllers\API\TowerController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\VehicleController;
use App\Http\Controllers\API\VisitorController;
use App\Http\Controllers\API\VisitorVehicleController;
use Illuminate\Support\Facades\Route;

// Health check endpoint
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now()->toISOString(),
        'version' => '1.0.0',
    ]);
});

Route::controller(AuthController::class)->group(function () {
    Route::post('auth/login', 'login');
    Route::post('auth/register', 'register');
    Route::post('auth/logout', 'logout');
    Route::post('auth/refresh', 'refresh');
    Route::get('auth/me', 'me');
});

Route::post('auth/passkeys/login/options', [PasskeyAuthController::class, 'options']);
Route::post('auth/passkeys/login/verify', [PasskeyAuthController::class, 'verify']);

Route::prefix('public/supplier-quotes')->middleware('throttle:30,1')->group(function () {
    Route::get('{token}', [PublicSupplierQuoteController::class, 'show']);
    Route::post('{token}', [PublicSupplierQuoteController::class, 'store']);
    Route::post('{token}/attachments', [PublicSupplierQuoteController::class, 'upload']);
});

Route::get('public/invites/{token}', [AccessRequestController::class, 'publicInvite'])->middleware('throttle:60,1');
Route::middleware(['auth:api', 'tenant'])->group(function () {
    Route::get('auth/current-condominium', [AuthController::class, 'currentCondominium']);
    Route::get('my-apartments', [AuthController::class, 'myApartments']);
    Route::get('profile', [ProfileSettingsController::class, 'show']);
    Route::put('profile', [ProfileSettingsController::class, 'update']);
    Route::put('profile/password', [ProfileSettingsController::class, 'updatePassword']);
    Route::put('profile/notifications', [ProfileSettingsController::class, 'updateNotifications']);

    Route::get('notifications/unread-count', [NotificationInboxController::class, 'unreadCount']);
    Route::post('notifications/read-all', [NotificationInboxController::class, 'readAll']);
    Route::post('notifications/{id}/read', [NotificationInboxController::class, 'markRead']);
    Route::get('notifications', [NotificationInboxController::class, 'index']);

    Route::get('condominiums/{condominium}/stats', [CondominiumController::class, 'stats']);
    Route::apiResource('condominiums', CondominiumController::class);
    Route::apiResource('residents', ResidentController::class);
    Route::put('residents/{resident}/link-user', [ResidentController::class, 'linkUser']);
    Route::apiResource('towers', TowerController::class);
    Route::apiResource('blocks', BlockController::class);
    Route::apiResource('apartments', ApartmentController::class);
    Route::apiResource('profiles', ProfileController::class);
    Route::apiResource('modules', ModuleController::class);
    Route::apiResource('users', UserController::class);

    Route::prefix('security')->name('security.')->group(function () {
        Route::get('passkeys', [SecurityPasskeyController::class, 'index']);
        Route::post('passkeys/register/options', [SecurityPasskeyController::class, 'registrationOptions']);
        Route::post('passkeys/register/verify', [SecurityPasskeyController::class, 'verifyRegistration']);
        Route::delete('passkeys/{passkey}', [SecurityPasskeyController::class, 'destroy']);
        Route::get('access-logs', [SecurityAdminController::class, 'accessLogs']);
        Route::get('users/{user}/passkeys', [SecurityAdminController::class, 'userPasskeys']);
        Route::delete('users/{user}/passkeys/{passkey}', [SecurityAdminController::class, 'revokeUserPasskey']);
    });

    // Profile Management Routes
    Route::prefix('profile-management')->name('profile-management.')->group(function () {
        Route::get('data', [ProfileManagementController::class, 'getManagementData'])->name('data');
        Route::post('profiles', [ProfileManagementController::class, 'createProfile'])->name('createProfile');
        Route::put('profiles/{profile}', [ProfileManagementController::class, 'updateProfile'])->name('updateProfile');
        Route::delete('profiles/{profile}', [ProfileManagementController::class, 'deleteProfile'])->name('deleteProfile');
        Route::post('profiles/{profile}/duplicate', [ProfileManagementController::class, 'duplicateProfile'])->name('duplicateProfile');
        Route::get('profiles/{profile}/users', [ProfileManagementController::class, 'getProfileUsers'])->name('getProfileUsers');
        Route::get('privileges-report', [ProfileManagementController::class, 'getPrivilegesReport'])->name('privilegesReport');
    });

    // Profile Analytics Dashboard Routes
    Route::prefix('profile-analytics')->name('profile-analytics.')->group(function () {
        Route::get('dashboard', [ProfileAnalyticsDashboardController::class, 'getDashboardData'])->name('dashboard');
    });
    Route::apiResource('parking-spots', ParkingSpotController::class);
    Route::apiResource('vehicles', VehicleController::class);
    Route::apiResource('pets', \App\Http\Controllers\API\PetController::class);
    Route::apiResource('common-areas', CommonAreaController::class);
    Route::apiResource('packages', \App\Http\Controllers\API\PackageController::class);
    Route::get('reports/operational', [OperationalReportController::class, 'show']);
    Route::get('reports/operational/export', [OperationalReportController::class, 'export']);

    // Notice and Document Routes
    Route::apiResource('notices', \App\Http\Controllers\API\NoticeController::class);
    Route::get('documents', [\App\Http\Controllers\API\DocumentController::class, 'index'])->name('documents.index');

    // Common Area Bookings Routes
    Route::get('common-area-bookings', [CommonAreaController::class, 'allBookings']);
    Route::get('common-area-bookings/{id}', [CommonAreaController::class, 'showBooking']);
    Route::get('common-areas/{common_area}/bookings', [CommonAreaController::class, 'bookings']);
    Route::post('common-areas/{common_area}/bookings', [CommonAreaController::class, 'book']);
    Route::patch('common-areas/{common_area}/bookings/{booking}/cancel', [CommonAreaController::class, 'cancelBooking']);
    Route::patch('common-areas/{common_area}/bookings/{booking}/approve', [CommonAreaController::class, 'approveBooking']);
    Route::patch('common-areas/{common_area}/bookings/{booking}/reject', [CommonAreaController::class, 'rejectBooking']);
    Route::get('common-areas/{common_area}/available-slots', [CommonAreaController::class, 'getAvailableSlots']);

    Route::post('parking-spots/batch', [ParkingSpotController::class, 'batchStore']);
    Route::post('parking-spots/{parking_spot}/rent', [ParkingSpotController::class, 'rent']);
    Route::get('packages/by-code/{code}', [\App\Http\Controllers\API\PackageController::class, 'showByCode']);
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
    Route::post('occurrences/{occurrence}/convert-to-maintenance', [OccurrenceController::class, 'convertToMaintenance']);

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

    // Access Control Module Routes
    Route::prefix('access-control')->name('access-control.')->group(function () {
        Route::get('dashboard', [AccessControlDashboardController::class, 'getDashboardData'])->name('dashboard');
        // Visitors
        Route::get('visitors/search-by-document', [VisitorController::class, 'searchByDocument'])->name('visitors.searchByDocument');
        Route::apiResource('visitors', VisitorController::class);

        // Visitor Vehicles (nested under visitors)
        Route::apiResource('visitors.vehicles', VisitorVehicleController::class)->shallow();
        // Example: GET /api/access-control/visitors/{visitor}/vehicles
        // GET /api/access-control/vehicles/{vehicle} (due to shallow)

        // Protective Measures
        Route::apiResource('protective-measures', ProtectiveMeasureController::class);

        // Access Requests
        Route::get('check-protective-measure', [AccessRequestController::class, 'checkProtectiveMeasure'])->name('checkProtectiveMeasure');
        Route::get('access-requests', [AccessRequestController::class, 'index'])->name('access-requests.index');
        Route::post('access-requests', [AccessRequestController::class, 'store'])->name('access-requests.store');
        Route::get('access-requests/history', [AccessRequestController::class, 'history'])->name('access-requests.history');
        Route::get('access-requests/gatehouse', [AccessRequestController::class, 'listForGatehouse'])->name('access-requests.listForGatehouse');
        Route::get('access-requests/my-requests', [AccessRequestController::class, 'listMyRequestsAsResident'])->name('access-requests.listMyRequestsAsResident');
        Route::get('access-requests/by-token/{token}', [AccessRequestController::class, 'showByToken'])->name('access-requests.showByToken');
        Route::get('access-requests/{access_request}', [AccessRequestController::class, 'show'])->name('access-requests.show');
        Route::post('access-requests/{access_request}/respond', [AccessRequestController::class, 'respondToRequest'])->name('access-requests.respond');
        Route::post('access-requests/{access_request}/record-entry', [AccessRequestController::class, 'recordEntry'])->name('access-requests.recordEntry');
        Route::post('access-requests/{access_request}/record-exit', [AccessRequestController::class, 'recordExit'])->name('access-requests.recordExit');
        Route::post('access-requests/{access_request}/cancel', [AccessRequestController::class, 'cancelRequest'])->name('access-requests.cancel');
    });

    // Módulo de Assembleias Virtuais e Votações Digitais
    Route::apiResource('assemblies', AssemblyController::class);
    Route::post('assemblies/{assembly}/start', [AssemblyController::class, 'start']);
    Route::post('assemblies/{assembly}/close', [AssemblyController::class, 'close']);
    Route::post('assemblies/{assembly}/agendas', [AssemblyController::class, 'addAgenda']);
    Route::post('assemblies/{assembly}/agendas/{agenda}/open', [AssemblyController::class, 'openAgendaVoting']);
    Route::post('assemblies/{assembly}/agendas/{agenda}/close', [AssemblyController::class, 'closeAgendaVoting']);
    Route::post('assemblies/{assembly}/agendas/{agenda}/vote', [AssemblyController::class, 'vote']);
    Route::get('assemblies/{assembly}/agendas/{agenda}/results', [AssemblyController::class, 'results']);

    // Finance Module Routes
    Route::prefix('finance')->name('finance.')->group(function () {
        Route::apiResource('banks', \App\Http\Controllers\API\BankController::class);
        Route::apiResource('chart-accounts', \App\Http\Controllers\API\ChartAccountController::class);
        Route::apiResource('transactions', \App\Http\Controllers\API\TransactionController::class)->only(['index', 'store']);
        Route::apiResource('payables', \App\Http\Controllers\API\PayableController::class);
        Route::apiResource('payables.attachments', \App\Http\Controllers\API\PayableAttachmentController::class)->except(['update']);
        Route::apiResource('receivables', \App\Http\Controllers\API\ReceivableController::class);
        Route::apiResource('receivables.attachments', \App\Http\Controllers\API\ReceivableAttachmentController::class)->except(['update']);
        Route::apiResource('cash-flows', \App\Http\Controllers\API\CashFlowController::class);

        // Budget Items
        Route::post('budget-items/bulk', [\App\Http\Controllers\API\BudgetItemController::class, 'bulkUpdate']);
        Route::apiResource('budget-items', \App\Http\Controllers\API\BudgetItemController::class);

        Route::post('bank-statements/upload', [\App\Http\Controllers\API\BankStatementController::class, 'upload']);
        Route::post('accounts/reconcile', [\App\Http\Controllers\API\AccountReconciliationController::class, 'reconcile']);
        Route::get('accounts/history', [\App\Http\Controllers\API\AccountReconciliationController::class, 'history']);
        Route::apiResource('expense-requests', \App\Http\Controllers\API\ExpenseRequestController::class);
        Route::apiResource('expense-requests.attachments', \App\Http\Controllers\API\ExpenseRequestAttachmentController::class)->except(['update']);
        Route::post('expense-requests/{expense_request}/approve', [\App\Http\Controllers\API\ExpenseRequestController::class, 'approve']);
        Route::post('expense-requests/{expense_request}/submit', [\App\Http\Controllers\API\ExpenseRequestController::class, 'submit']);
        Route::post('expense-requests/{expense_request}/decision', [\App\Http\Controllers\API\ExpenseRequestController::class, 'decide']);
        Route::post('expense-requests/{expense_request}/convert-to-payable', [\App\Http\Controllers\API\ExpenseRequestController::class, 'convertToPayable']);
        Route::apiResource('expense-approval-rules', \App\Http\Controllers\API\ExpenseApprovalRuleController::class)->except(['show']);

        Route::post('payables/{payable}/settlements', [\App\Http\Controllers\API\FinanceSettlementController::class, 'storePayable']);
        Route::post('receivables/{receivable}/settlements', [\App\Http\Controllers\API\FinanceSettlementController::class, 'storeReceivable']);
        Route::get('dashboard', [\App\Http\Controllers\API\FinanceDashboardController::class, 'forecast']);
        Route::get('dashboard/forecast', [\App\Http\Controllers\API\FinanceDashboardController::class, 'forecast']);

        Route::apiResource('procurements', ProcurementController::class)->parameters(['procurements' => 'procurement_request'])->only(['index', 'store', 'show']);
        Route::post('procurements/{procurement_request}/publish', [ProcurementController::class, 'publish']);
        Route::post('procurements/{procurement_request}/invitations', [ProcurementController::class, 'invite']);
        Route::post('procurements/{procurement_request}/close', [ProcurementController::class, 'close']);
        Route::post('procurements/{procurement_request}/expense-requests', [ProcurementController::class, 'createExpenseRequest']);

        Route::post('webhooks/stripe', [\App\Http\Controllers\API\FinanceWebhookController::class, 'stripe']);
        Route::post('webhooks/mercadopago', [\App\Http\Controllers\API\FinanceWebhookController::class, 'mercadoPago']);
        Route::post('webhooks/cnab', [\App\Http\Controllers\API\FinanceWebhookController::class, 'cnab']);

        Route::get('reports/dre', [\App\Http\Controllers\API\FinanceReportController::class, 'dre']);
        Route::get('reports/balancete', [\App\Http\Controllers\API\FinanceReportController::class, 'balancete']);
        Route::get('reports/budget', [\App\Http\Controllers\API\BudgetReportController::class, 'getReport']);
    });
});

// Public QR Code Report Route
Route::get('/qr/{qr_code_hash}', [\App\Http\Controllers\API\PublicMaintenanceReportController::class, 'showReportByQrCode'])
    ->name('public.maintenance.report');
