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
    ->middleware('auth:sanctum')
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
        Route::prefix('properties')->name('properties.')->group(function (): void {
            Route::post('{property}/archive', [PropertyController::class, 'archive'])->name('archive');
        });
        Route::apiResource('properties', PropertyController::class);

        /*
        |--------------------------------------------------------------------------
        | Owners
        |--------------------------------------------------------------------------
        */
        Route::prefix('owners')->name('owners.')->group(function (): void {
            Route::post('{owner}/archive', [OwnerController::class, 'archive'])->name('archive');
            Route::post('{owner}/restore', [OwnerController::class, 'restore'])->name('restore');
        });
        Route::apiResource('owners', OwnerController::class);

        /*
        |--------------------------------------------------------------------------
        | Clients
        |--------------------------------------------------------------------------
        */
        Route::prefix('clients')->name('clients.')->group(function (): void {
            Route::post('{client}/archive', [ClientController::class, 'archive'])->name('archive');
            Route::post('{client}/restore', [ClientController::class, 'restore'])->name('restore');
        });
        Route::apiResource('clients', ClientController::class);

        /*
        |--------------------------------------------------------------------------
        | Providers
        |--------------------------------------------------------------------------
        */
        Route::prefix('providers')->name('providers.')->group(function (): void {
            Route::post('{provider}/activate', [ProviderController::class, 'activate'])->name('activate');
            Route::post('{provider}/deactivate', [ProviderController::class, 'deactivate'])->name('deactivate');
        });
        Route::apiResource('providers', ProviderController::class);

        /*
        |--------------------------------------------------------------------------
        | Contracts
        |--------------------------------------------------------------------------
        */
        Route::prefix('contracts')->name('contracts.')->group(function (): void {
            Route::post('{contract}/renew', [ContractController::class, 'renew'])->name('renew');
            Route::post('{contract}/archive', [ContractController::class, 'archive'])->name('archive');
        });
        Route::apiResource('contracts', ContractController::class);

        /*
        |--------------------------------------------------------------------------
        | Rentals
        |--------------------------------------------------------------------------
        */
        Route::prefix('rentals')->name('rentals.')->group(function (): void {
            Route::post('{rentalUnit}/activate', [RentalUnitController::class, 'activate'])->name('activate');
            Route::post('{rentalUnit}/end', [RentalUnitController::class, 'end'])->name('end');
            Route::post('{rentalUnit}/cancel', [RentalUnitController::class, 'cancel'])->name('cancel');
            Route::post('{rentalUnit}/renew', [RentalUnitController::class, 'renew'])->name('renew');
        });
        Route::apiResource('rentals', RentalUnitController::class)
            ->parameters(['rentals' => 'rentalUnit']);

        /*
        |--------------------------------------------------------------------------
        | Complaints
        |--------------------------------------------------------------------------
        */
        Route::prefix('complaints')->name('complaints.')->group(function (): void {
            Route::post('{complaint}/assign-to-user', [ComplaintController::class, 'assignToUser'])->name('assign-to-user');
            Route::post('{complaint}/mark-seen', [ComplaintController::class, 'markSeen'])->name('mark-seen');
            Route::post('{complaint}/mark-in-progress', [ComplaintController::class, 'markInProgress'])->name('mark-in-progress');
            Route::post('{complaint}/mark-waiting-provider', [ComplaintController::class, 'markWaitingProvider'])->name('mark-waiting-provider');
            Route::post('{complaint}/resolve', [ComplaintController::class, 'resolve'])->name('resolve');
            Route::post('{complaint}/close', [ComplaintController::class, 'close'])->name('close');
            Route::post('{complaint}/reopen', [ComplaintController::class, 'reopen'])->name('reopen');
        });
        Route::apiResource('complaints', ComplaintController::class);

        /*
        |--------------------------------------------------------------------------
        | Appointments
        |--------------------------------------------------------------------------
        */
        Route::prefix('appointments')->name('appointments.')->group(function (): void {
            Route::post('{appointment}/cancel', [AppointmentController::class, 'cancel'])->name('cancel');
            Route::post('{appointment}/complete', [AppointmentController::class, 'complete'])->name('complete');
            Route::post('{appointment}/mark-no-show', [AppointmentController::class, 'markNoShow'])->name('mark-no-show');
        });
        Route::apiResource('appointments', AppointmentController::class);

        /*
        |--------------------------------------------------------------------------
        | Collaborations
        |--------------------------------------------------------------------------
        */
        Route::prefix('collaborations')->name('collaborations.')->group(function (): void {
            Route::post('{collaboration}/accept', [CollaborationController::class, 'accept'])->name('accept');
            Route::post('{collaboration}/reject', [CollaborationController::class, 'reject'])->name('reject');
            Route::post('{collaboration}/cancel', [CollaborationController::class, 'cancel'])->name('cancel');
            Route::post('{collaboration}/complete', [CollaborationController::class, 'complete'])->name('complete');
            Route::post('{collaboration}/messages', [CollaborationController::class, 'addMessage'])->name('messages.store');
            Route::post('{collaboration}/documents', [CollaborationController::class, 'addDocument'])->name('documents.store');
            Route::post('{collaboration}/visits', [CollaborationController::class, 'scheduleVisit'])->name('visits.store');
            Route::post('{collaboration}/offers', [CollaborationController::class, 'submitOffer'])->name('offers.store');
        });
        Route::apiResource('collaborations', CollaborationController::class);

        /*
        |--------------------------------------------------------------------------
        | Financial
        |--------------------------------------------------------------------------
        */
        Route::prefix('financial')->name('financial.')->group(function (): void {
            Route::get('dashboard', [FinancialController::class, 'dashboard'])->name('dashboard');
            Route::post('close-period', [FinancialController::class, 'closePeriod'])->name('close-period');
            Route::post('{financialTransaction}/validate', [FinancialController::class, 'validateTransaction'])->name('validate');
            Route::post('{financialTransaction}/cancel', [FinancialController::class, 'cancelTransaction'])->name('cancel');
        });
        Route::apiResource('financial', FinancialController::class)
            ->parameters(['financial' => 'financialTransaction']);

        /*
        |--------------------------------------------------------------------------
        | Invoices
        |--------------------------------------------------------------------------
        */
        Route::prefix('invoices')->name('invoices.')->group(function (): void {
            Route::post('{invoice}/mark-sent', [InvoiceController::class, 'markSent'])->name('mark-sent');
            Route::post('{invoice}/mark-paid', [InvoiceController::class, 'markPaid'])->name('mark-paid');
            Route::post('{invoice}/mark-partially-paid', [InvoiceController::class, 'markPartiallyPaid'])->name('mark-partially-paid');
            Route::post('{invoice}/mark-overdue', [InvoiceController::class, 'markOverdue'])->name('mark-overdue');
            Route::post('{invoice}/cancel', [InvoiceController::class, 'cancel'])->name('cancel');
            Route::post('{invoice}/duplicate', [InvoiceController::class, 'duplicate'])->name('duplicate');
            Route::post('{invoice}/generate-pdf', [InvoiceController::class, 'generatePdf'])->name('generate-pdf');
        });
        Route::apiResource('invoices', InvoiceController::class);

        /*
        |--------------------------------------------------------------------------
        | Dashboard
        |--------------------------------------------------------------------------
        */
        Route::prefix('dashboard')->name('dashboard.')->group(function (): void {
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
        Route::prefix('reports')->name('reports.')->group(function (): void {
            Route::post('{report}/generate', [ReportController::class, 'generate'])->name('generate');
            Route::post('{report}/export-pdf', [ReportController::class, 'exportPdf'])->name('export-pdf');
            Route::post('{report}/export-excel', [ReportController::class, 'exportExcel'])->name('export-excel');
            Route::post('{report}/export-csv', [ReportController::class, 'exportCsv'])->name('export-csv');
            Route::post('{report}/schedule', [ReportController::class, 'schedule'])->name('schedule');
            Route::post('{report}/share', [ReportController::class, 'share'])->name('share');
        });
        Route::apiResource('reports', ReportController::class);

        /*
        |--------------------------------------------------------------------------
        | Notifications
        |--------------------------------------------------------------------------
        */
        Route::prefix('notifications')->name('notifications.')->group(function (): void {
            Route::post('mark-all-as-read', [NotificationController::class, 'markAllAsRead'])->name('mark-all-as-read');
            Route::post('{notification}/mark-as-read', [NotificationController::class, 'markAsRead'])->name('mark-as-read');
            Route::post('{notification}/archive', [NotificationController::class, 'archive'])->name('archive');
            Route::post('{notification}/restore', [NotificationController::class, 'restore'])->name('restore');
            Route::post('{notification}/send-now', [NotificationController::class, 'sendNow'])->name('send-now');
            Route::post('{notification}/queue', [NotificationController::class, 'queue'])->name('queue');
        });
        Route::apiResource('notifications', NotificationController::class);

        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        */
        Route::prefix('search')->name('search.')->group(function (): void {
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
        Route::prefix('settings')->name('settings.')->group(function (): void {
            // Reserved for settings endpoints when a settings API controller is introduced.
        });
    });
