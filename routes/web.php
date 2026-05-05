<?php

use App\Http\Controllers\ClaimController;
use App\Http\Controllers\ApprovalController;
use App\Http\Controllers\CRMController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DelegationController;
use App\Http\Controllers\FinanceController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\PolicyController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RenewalController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ReinsuranceController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

Route::post('/locale', LocaleController::class)->name('locale.switch');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::middleware('role:SUPER_ADMIN,BRANCH_MANAGER,UNDERWRITER,BROKER,CLIENT')->group(function () {
        Route::get('/policies', [PolicyController::class, 'index'])->name('policies.index');
        Route::get('/policies/{policy}', [PolicyController::class, 'show'])->whereNumber('policy')->name('policies.show');
        Route::get('/policies/{policy}/pdf', [PolicyController::class, 'exportPdf'])->whereNumber('policy')->name('policies.pdf');
    });
    Route::middleware('role:SUPER_ADMIN,BRANCH_MANAGER,UNDERWRITER')->group(function () {
        Route::get('/policies/create', [PolicyController::class, 'create'])->name('policies.create');
        Route::post('/policies', [PolicyController::class, 'store'])->name('policies.store');
        Route::get('/policies/{policy}/edit', [PolicyController::class, 'edit'])->whereNumber('policy')->name('policies.edit');
        Route::put('/policies/{policy}', [PolicyController::class, 'update'])->whereNumber('policy')->name('policies.update');
        Route::delete('/policies/{policy}', [PolicyController::class, 'destroy'])->whereNumber('policy')->name('policies.destroy');
    });

    Route::middleware('role:SUPER_ADMIN,BRANCH_MANAGER,CLAIMS_OFFICER,BROKER,CLIENT')->group(function () {
        Route::get('/claims', [ClaimController::class, 'index'])->name('claims.index');
        Route::get('/claims/{claim}', [ClaimController::class, 'show'])->whereNumber('claim')->name('claims.show');
    });
    Route::middleware('role:SUPER_ADMIN,BRANCH_MANAGER,CLAIMS_OFFICER')->group(function () {
        Route::get('/claims/create', [ClaimController::class, 'create'])->name('claims.create');
        Route::post('/claims', [ClaimController::class, 'store'])->name('claims.store');
        Route::get('/claims/{claim}/edit', [ClaimController::class, 'edit'])->whereNumber('claim')->name('claims.edit');
        Route::put('/claims/{claim}', [ClaimController::class, 'update'])->whereNumber('claim')->name('claims.update');
        Route::delete('/claims/{claim}', [ClaimController::class, 'destroy'])->whereNumber('claim')->name('claims.destroy');
    });

    Route::middleware('role:SUPER_ADMIN,BRANCH_MANAGER,BROKER')->group(function () {
        Route::get('/crm', [CRMController::class, 'index'])->name('crm.index');
    });
    Route::middleware('role:SUPER_ADMIN,BRANCH_MANAGER,ACCOUNTANT')->group(function () {
        Route::get('/reinsurance', [ReinsuranceController::class, 'index'])->name('reinsurance.index');
        Route::get('/reinsurance/export/monthly', [ReinsuranceController::class, 'exportMonthly'])->name('reinsurance.export');
        Route::get('/finance', [FinanceController::class, 'index'])->name('finance.index');
    });
    Route::middleware('role:SUPER_ADMIN,BRANCH_MANAGER,UNDERWRITER,ACCOUNTANT,CLAIMS_OFFICER')->group(function () {
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/export/excel', [ReportController::class, 'exportExcel'])->name('reports.excel');
        Route::get('/reports/export/pdf', [ReportController::class, 'exportPdf'])->name('reports.pdf');
    });
    Route::view('/settings', 'placeholders.settings')->name('settings.index');

    Route::middleware('role:SUPER_ADMIN,BRANCH_MANAGER,UNDERWRITER,CLAIMS_OFFICER,ACCOUNTANT')->group(function () {
        Route::get('/approvals', [ApprovalController::class, 'index'])->name('approvals.index');
        Route::post('/approvals/{approvalRequest}/approve', [ApprovalController::class, 'approve'])->whereNumber('approvalRequest')->name('approvals.approve');
        Route::post('/approvals/{approvalRequest}/reject', [ApprovalController::class, 'reject'])->whereNumber('approvalRequest')->name('approvals.reject');
    });

    Route::middleware('role:SUPER_ADMIN,BRANCH_MANAGER,UNDERWRITER,BROKER,CLIENT')->group(function () {
        Route::get('/renewals', [RenewalController::class, 'index'])->name('renewals.index');
        Route::get('/policies/{policy}/renewal-suggest', [RenewalController::class, 'suggest'])->whereNumber('policy')->name('policies.renewal-suggest');
        Route::post('/renewals/bulk', [RenewalController::class, 'bulkRenew'])->name('renewals.bulk');
        Route::post('/policies/{policy}/renew', [RenewalController::class, 'renew'])->whereNumber('policy')->name('policies.renew');
    });

    Route::middleware('role:SUPER_ADMIN,BRANCH_MANAGER')->group(function () {
        Route::get('/delegations', [DelegationController::class, 'index'])->name('delegations.index');
        Route::post('/delegations', [DelegationController::class, 'store'])->name('delegations.store');
        Route::delete('/delegations/{userDelegation}', [DelegationController::class, 'destroy'])->whereNumber('userDelegation')->name('delegations.destroy');
    });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
