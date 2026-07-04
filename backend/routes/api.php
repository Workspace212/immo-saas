<?php

declare(strict_types=1);

use App\Http\Controllers\Api\AppointmentController;
use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\Api\CollaborationController;
use App\Http\Controllers\Api\ComplaintController;
use App\Http\Controllers\Api\ContractController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\FinancialController;
use App\Http\Controllers\Api\InvoiceController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\OwnerController;
use App\Http\Controllers\Api\PropertyController;
use App\Http\Controllers\Api\ProviderController;
use App\Http\Controllers\Api\RentalUnitController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\SearchController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')
    ->name('v1.')
    ->middleware(['auth:sanctum', 'tenant.agency'])
    ->group(function (): void {
        /*
        |--------------------------------------------------------------------------
        | Auth
        |--------------------------------------------------------------------------
        */
        Route::prefix('auth')->name('auth.')->group(function (): void {
            // Reserved for authenticated profile/session endpoints.
        });

        /*
        |--------------------------------------------------------------------------
        | Properties
        |--------------------------------------------------------------------------
        */
        // Coarse module gate only; resource ownership still belongs in policies.
        Route::prefix('properties')->name('properties.')->middleware('permission:properties.viewAny|properties.view|properties.create|properties.update|properties.archive|properties.restore|properties.delete|properties.export')->group(function (): void {
            Route::post('{property}/archive', [PropertyController::class, 'archive'])->name('archive');
        });
        Route::apiResource('properties', PropertyController::class)
            ->middleware('permission:properties.viewAny|properties.view|properties.create|properties.update|properties.archive|properties.restore|properties.delete|properties.export');

        /*
        |--------------------------------------------------------------------------
        | Owners
        |--------------------------------------------------------------------------
        */
        // Coarse module gate only; resource ownership still belongs in policies.
        Route::prefix('owners')->name('owners.')->middleware('permission:owners.viewAny|owners.view|owners.create|owners.update|owners.archive|owners.restore|owners.delete')->group(function (): void {
            Route::post('{owner}/archive', [OwnerController::class, 'archive'])->name('archive');
            Route::post('{owner}/restore', [OwnerController::class, 'restore'])->name('restore');
        });
        Route::apiResource('owners', OwnerController::class)
            ->middleware('permission:owners.viewAny|owners.view|owners.create|owners.update|owners.archive|owners.restore|owners.delete');

        /*
        |--------------------------------------------------------------------------
        | Clients
        |--------------------------------------------------------------------------
        */
        // Coarse module gate only; resource ownership still belongs in policies.
        Route::prefix('clients')->name('clients.')->middleware('permission:clients.viewAny|clients.view|clients.create|clients.update|clients.archive|clients.restore|clients.delete')->group(function (): void {
            Route::post('{client}/archive', [ClientController::class, 'archive'])->name('archive');
            Route::post('{client}/restore', [ClientController::class, 'restore'])->name('restore');
        });
        Route::apiResource('clients', ClientController::class)
            ->middleware('permission:clients.viewAny|clients.view|clients.create|clients.update|clients.archive|clients.restore|clients.delete');

        /*
        |--------------------------------------------------------------------------
        | Providers
        |--------------------------------------------------------------------------
        */
        // Coarse module gate only; resource ownership still belongs in policies.
        Route::prefix('providers')->name('providers.')->middleware('permission:providers.viewAny|providers.view|providers.create|providers.update|providers.archive|providers.restore|providers.delete')->group(function (): void {
            Route::post('{provider}/activate', [ProviderController::class, 'activate'])->name('activate');
            Route::post('{provider}/deactivate', [ProviderController::class, 'deactivate'])->name('deactivate');
        });
        Route::apiResource('providers', ProviderController::class)
            ->middleware('permission:providers.viewAny|providers.view|providers.create|providers.update|providers.archive|providers.restore|providers.delete');

        /*
        |--------------------------------------------------------------------------
        | Contracts
        |--------------------------------------------------------------------------
        */
        // Coarse module gate only; resource ownership still belongs in policies.
        Route::prefix('contracts')->name('contracts.')->middleware('permission:contracts.viewAny|contracts.view|contracts.create|contracts.update|contracts.archive|contracts.restore|contracts.delete|contracts.validate')->group(function (): void {
            Route::post('{contract}/renew', [ContractController::class, 'renew'])->name('renew');
            Route::post('{contract}/archive', [ContractController::class, 'archive'])->name('archive');
        });
        Route::apiResource('contracts', ContractController::class)
            ->middleware('permission:contracts.viewAny|contracts.view|contracts.create|contracts.update|contracts.archive|contracts.restore|contracts.delete|contracts.validate');

        /*
        |--------------------------------------------------------------------------
        | Rentals
        |--------------------------------------------------------------------------
        */
        // Coarse module gate only; resource ownership still belongs in policies.
        Route::prefix('rentals')->name('rentals.')->middleware('permission:rentals.viewAny|rentals.view|rentals.create|rentals.update|rentals.archive|rentals.restore|rentals.delete')->group(function (): void {
            Route::post('{rentalUnit}/activate', [RentalUnitController::class, 'activate'])->name('activate');
            Route::post('{rentalUnit}/end', [RentalUnitController::class, 'end'])->name('end');
            Route::post('{rentalUnit}/cancel', [RentalUnitController::class, 'cancel'])->name('cancel');
            Route::post('{rentalUnit}/renew', [RentalUnitController::class, 'renew'])->name('renew');
        });
        Route::apiResource('rentals', RentalUnitController::class)
            ->parameters(['rentals' => 'rentalUnit'])
            ->middleware('permission:rentals.viewAny|rentals.view|rentals.create|rentals.update|rentals.archive|rentals.restore|rentals.delete');

        /*
        |--------------------------------------------------------------------------
        | Complaints
        |--------------------------------------------------------------------------
        */
        // Coarse module gate only; resource ownership still belongs in policies.
        Route::prefix('complaints')->name('complaints.')->middleware('permission:complaints.viewAny|complaints.view|complaints.create|complaints.update|complaints.archive|complaints.restore|complaints.delete|complaints.manage')->group(function (): void {
            Route::post('{complaint}/assign-to-user', [ComplaintController::class, 'assignToUser'])->name('assign-to-user');
            Route::post('{complaint}/mark-seen', [ComplaintController::class, 'markSeen'])->name('mark-seen');
            Route::post('{complaint}/mark-in-progress', [ComplaintController::class, 'markInProgress'])->name('mark-in-progress');
            Route::post('{complaint}/mark-waiting-provider', [ComplaintController::class, 'markWaitingProvider'])->name('mark-waiting-provider');
            Route::post('{complaint}/resolve', [ComplaintController::class, 'resolve'])->name('resolve');
            Route::post('{complaint}/close', [ComplaintController::class, 'close'])->name('close');
            Route::post('{complaint}/reopen', [ComplaintController::class, 'reopen'])->name('reopen');
        });
        Route::apiResource('complaints', ComplaintController::class)
            ->middleware('permission:complaints.viewAny|complaints.view|complaints.create|complaints.update|complaints.archive|complaints.restore|complaints.delete|complaints.manage');

        /*
        |--------------------------------------------------------------------------
        | Appointments
        |--------------------------------------------------------------------------
        */
        // Coarse module gate only; resource ownership still belongs in policies.
        Route::prefix('appointments')->name('appointments.')->middleware('permission:appointments.viewAny|appointments.view|appointments.create|appointments.update|appointments.archive|appointments.restore|appointments.delete')->group(function (): void {
            Route::post('{appointment}/cancel', [AppointmentController::class, 'cancel'])->name('cancel');
            Route::post('{appointment}/complete', [AppointmentController::class, 'complete'])->name('complete');
            Route::post('{appointment}/mark-no-show', [AppointmentController::class, 'markNoShow'])->name('mark-no-show');
        });
        Route::apiResource('appointments', AppointmentController::class)
            ->middleware('permission:appointments.viewAny|appointments.view|appointments.create|appointments.update|appointments.archive|appointments.restore|appointments.delete');

        /*
        |--------------------------------------------------------------------------
        | Collaborations
        |--------------------------------------------------------------------------
        */
        // Coarse module gate only; resource ownership still belongs in policies.
        Route::prefix('collaborations')->name('collaborations.')->middleware('permission:collaborations.viewAny|collaborations.view|collaborations.create|collaborations.update|collaborations.archive|collaborations.restore|collaborations.delete|collaborations.share')->group(function (): void {
            Route::post('{collaboration}/accept', [CollaborationController::class, 'accept'])->name('accept');
            Route::post('{collaboration}/reject', [CollaborationController::class, 'reject'])->name('reject');
            Route::post('{collaboration}/cancel', [CollaborationController::class, 'cancel'])->name('cancel');
            Route::post('{collaboration}/complete', [CollaborationController::class, 'complete'])->name('complete');
            Route::post('{collaboration}/messages', [CollaborationController::class, 'addMessage'])->name('messages.store');
            Route::post('{collaboration}/documents', [CollaborationController::class, 'addDocument'])->name('documents.store');
            Route::post('{collaboration}/visits', [CollaborationController::class, 'scheduleVisit'])->name('visits.store');
            Route::post('{collaboration}/offers', [CollaborationController::class, 'submitOffer'])->name('offers.store');
        });
        Route::apiResource('collaborations', CollaborationController::class)
            ->middleware('permission:collaborations.viewAny|collaborations.view|collaborations.create|collaborations.update|collaborations.archive|collaborations.restore|collaborations.delete|collaborations.share');

        /*
        |--------------------------------------------------------------------------
        | Financial
        |--------------------------------------------------------------------------
        */
        // Coarse module gate only; transaction-level authorization still belongs in policies.
        Route::prefix('financial')->name('financial.')->middleware('permission:accounting.viewAny|accounting.view|accounting.create|accounting.update|accounting.validate|accounting.manage|accounting.export')->group(function (): void {
            Route::get('dashboard', [FinancialController::class, 'dashboard'])->name('dashboard');
            Route::post('close-period', [FinancialController::class, 'closePeriod'])->name('close-period');
            Route::post('{financialTransaction}/validate', [FinancialController::class, 'validateTransaction'])->name('validate');
            Route::post('{financialTransaction}/cancel', [FinancialController::class, 'cancelTransaction'])->name('cancel');
        });
        Route::apiResource('financial', FinancialController::class)
            ->parameters(['financial' => 'financialTransaction'])
            ->middleware('permission:accounting.viewAny|accounting.view|accounting.create|accounting.update|accounting.validate|accounting.manage|accounting.export');

        /*
        |--------------------------------------------------------------------------
        | Invoices
        |--------------------------------------------------------------------------
        */
        // Coarse module gate only; invoice ownership still belongs in policies.
        Route::prefix('invoices')->name('invoices.')->middleware('permission:invoices.viewAny|invoices.view|invoices.create|invoices.update|invoices.archive|invoices.restore|invoices.delete|invoices.validate|invoices.export')->group(function (): void {
            Route::post('{invoice}/mark-sent', [InvoiceController::class, 'markSent'])->name('mark-sent');
            Route::post('{invoice}/mark-paid', [InvoiceController::class, 'markPaid'])->name('mark-paid');
            Route::post('{invoice}/mark-partially-paid', [InvoiceController::class, 'markPartiallyPaid'])->name('mark-partially-paid');
            Route::post('{invoice}/mark-overdue', [InvoiceController::class, 'markOverdue'])->name('mark-overdue');
            Route::post('{invoice}/cancel', [InvoiceController::class, 'cancel'])->name('cancel');
            Route::post('{invoice}/duplicate', [InvoiceController::class, 'duplicate'])->name('duplicate');
            Route::post('{invoice}/generate-pdf', [InvoiceController::class, 'generatePdf'])->name('generate-pdf');
        });
        Route::apiResource('invoices', InvoiceController::class)
            ->middleware('permission:invoices.viewAny|invoices.view|invoices.create|invoices.update|invoices.archive|invoices.restore|invoices.delete|invoices.validate|invoices.export');

        /*
        |--------------------------------------------------------------------------
        | Dashboard
        |--------------------------------------------------------------------------
        */
        // Coarse module gate only; widget/layout ownership still belongs in policies.
        Route::prefix('dashboard')->name('dashboard.')->middleware('permission:dashboard.view|dashboard.manage')->group(function (): void {
            Route::get('overview', [DashboardController::class, 'overview'])->name('overview');
            Route::get('statistics', [DashboardController::class, 'statistics'])->name('statistics');
            Route::get('charts', [DashboardController::class, 'charts'])->name('charts');
            Route::get('agenda', [DashboardController::class, 'agenda'])->name('agenda');
            Route::get('notifications', [DashboardController::class, 'notifications'])->name('notifications');
            Route::get('favorites', [DashboardController::class, 'favorites'])->name('favorites');
            Route::get('layouts', [DashboardController::class, 'layouts'])->name('layouts');
            Route::get('snapshots', [DashboardController::class, 'snapshots'])->name('snapshots.index');
            Route::post('snapshots', [DashboardController::class, 'storeSnapshot'])->name('snapshots.store');
            Route::match(['put', 'patch'], 'snapshots/{snapshot}', [DashboardController::class, 'updateSnapshot'])->name('snapshots.update');
            Route::delete('snapshots/{snapshot}', [DashboardController::class, 'deleteSnapshot'])->name('snapshots.destroy');
        });

        /*
        |--------------------------------------------------------------------------
        | Reports
        |--------------------------------------------------------------------------
        */
        // Coarse module gate only; report ownership/sharing still belongs in policies.
        Route::prefix('reports')->name('reports.')->middleware('permission:reports.viewAny|reports.view|reports.create|reports.update|reports.archive|reports.restore|reports.delete|reports.export|reports.share|reports.manage')->group(function (): void {
            Route::post('{report}/generate', [ReportController::class, 'generate'])->name('generate');
            Route::post('{report}/export-pdf', [ReportController::class, 'exportPdf'])->name('export-pdf');
            Route::post('{report}/export-excel', [ReportController::class, 'exportExcel'])->name('export-excel');
            Route::post('{report}/export-csv', [ReportController::class, 'exportCsv'])->name('export-csv');
            Route::post('{report}/schedule', [ReportController::class, 'schedule'])->name('schedule');
            Route::post('{report}/share', [ReportController::class, 'share'])->name('share');
        });
        Route::apiResource('reports', ReportController::class)
            ->middleware('permission:reports.viewAny|reports.view|reports.create|reports.update|reports.archive|reports.restore|reports.delete|reports.export|reports.share|reports.manage');

        /*
        |--------------------------------------------------------------------------
        | Notifications
        |--------------------------------------------------------------------------
        */
        // Coarse module gate only; notification recipient checks still belong in policies.
        Route::prefix('notifications')->name('notifications.')->middleware('permission:notifications.viewAny|notifications.view|notifications.create|notifications.update|notifications.archive|notifications.restore|notifications.delete|notifications.manage')->group(function (): void {
            Route::post('mark-all-as-read', [NotificationController::class, 'markAllAsRead'])->name('mark-all-as-read');
            Route::post('{notification}/mark-as-read', [NotificationController::class, 'markAsRead'])->name('mark-as-read');
            Route::post('{notification}/archive', [NotificationController::class, 'archive'])->name('archive');
            Route::post('{notification}/restore', [NotificationController::class, 'restore'])->name('restore');
            Route::post('{notification}/send-now', [NotificationController::class, 'sendNow'])->name('send-now');
            Route::post('{notification}/queue', [NotificationController::class, 'queue'])->name('queue');
        });
        Route::apiResource('notifications', NotificationController::class)
            ->middleware('permission:notifications.viewAny|notifications.view|notifications.create|notifications.update|notifications.archive|notifications.restore|notifications.delete|notifications.manage');

        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        */
        // Coarse module gate only; searchable resource authorization still belongs in policies.
        Route::prefix('search')->name('search.')->middleware('permission:search.view|search.manage')->group(function (): void {
            Route::get('global', [SearchController::class, 'global'])->name('global');
            Route::get('properties', [SearchController::class, 'properties'])->name('properties');
            Route::get('owners', [SearchController::class, 'owners'])->name('owners');
            Route::get('clients', [SearchController::class, 'clients'])->name('clients');
            Route::get('contracts', [SearchController::class, 'contracts'])->name('contracts');
            Route::get('rentals', [SearchController::class, 'rentals'])->name('rentals');
            Route::get('complaints', [SearchController::class, 'complaints'])->name('complaints');
            Route::get('providers', [SearchController::class, 'providers'])->name('providers');
            Route::get('appointments', [SearchController::class, 'appointments'])->name('appointments');
            Route::get('collaborations', [SearchController::class, 'collaborations'])->name('collaborations');
            Route::get('financial', [SearchController::class, 'financial'])->name('financial');
            Route::get('invoices', [SearchController::class, 'invoices'])->name('invoices');
        });

        /*
        |--------------------------------------------------------------------------
        | Settings
        |--------------------------------------------------------------------------
        */
        Route::prefix('settings')->name('settings.')->middleware('permission:settings.view|settings.update|settings.manage')->group(function (): void {
            // Reserved for settings endpoints when a settings API controller is introduced.
        });
    });
