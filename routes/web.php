<?php

use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FollowUpActivityController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProspectContactController;
use App\Http\Controllers\ProspectController;
use App\Http\Controllers\QuotationApprovalController;
use App\Http\Controllers\QuotationController;
use App\Http\Controllers\QuotationPdfController;
use App\Http\Controllers\RentalPackageController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VehicleController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

Route::get('/dashboard', DashboardController::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::patch('/users/{user}/toggle-active', [UserController::class, 'toggleActive'])
        ->name('users.toggle-active');
    Route::resource('users', UserController::class)->except(['show', 'destroy']);

    Route::get('/vehicles/create', [VehicleController::class, 'create'])->name('vehicles.create');
    Route::post('/vehicles', [VehicleController::class, 'store'])->name('vehicles.store');
    Route::get('/vehicles/{vehicle}/edit', [VehicleController::class, 'edit'])->name('vehicles.edit');
    Route::put('/vehicles/{vehicle}', [VehicleController::class, 'update'])->name('vehicles.update');
    Route::patch('/vehicles/{vehicle}/toggle-active', [VehicleController::class, 'toggleActive'])->name('vehicles.toggle-active');

    Route::get('/rental-packages/create', [RentalPackageController::class, 'create'])->name('rental-packages.create');
    Route::post('/rental-packages', [RentalPackageController::class, 'store'])->name('rental-packages.store');
    Route::get('/rental-packages/{rentalPackage}/edit', [RentalPackageController::class, 'edit'])->name('rental-packages.edit');
    Route::put('/rental-packages/{rentalPackage}', [RentalPackageController::class, 'update'])->name('rental-packages.update');
    Route::patch('/rental-packages/{rentalPackage}/toggle-active', [RentalPackageController::class, 'toggleActive'])->name('rental-packages.toggle-active');
});

Route::middleware(['auth', 'role:admin,manager,finance'])->group(function () {
    Route::get('/vehicles', [VehicleController::class, 'index'])->name('vehicles.index');
    Route::get('/rental-packages', [RentalPackageController::class, 'index'])->name('rental-packages.index');
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/export/prospects', [ReportController::class, 'exportProspects'])->name('reports.exports.prospects');
    Route::get('/reports/export/quotations', [ReportController::class, 'exportQuotations'])->name('reports.exports.quotations');
    Route::get('/reports/exports/prospects', fn (Request $request) => redirect()
        ->route('reports.exports.prospects', $request->query()))
        ->name('reports.exports.prospects.legacy');
    Route::get('/reports/exports/quotations', fn (Request $request) => redirect()
        ->route('reports.exports.quotations', $request->query()))
        ->name('reports.exports.quotations.legacy');
});

Route::middleware(['auth', 'role:admin,sales,manager,finance'])->group(function () {
    Route::get('/prospects', [ProspectController::class, 'index'])->name('prospects.index');
});

Route::middleware(['auth', 'role:admin,sales'])->scopeBindings()->group(function () {
    Route::get('/prospects/create', [ProspectController::class, 'create'])->name('prospects.create');
    Route::post('/prospects', [ProspectController::class, 'store'])->name('prospects.store');
    Route::get('/prospects/{prospect}/edit', [ProspectController::class, 'edit'])->name('prospects.edit');
    Route::put('/prospects/{prospect}', [ProspectController::class, 'update'])->name('prospects.update');
    Route::delete('/prospects/{prospect}', [ProspectController::class, 'destroy'])->name('prospects.destroy');

    Route::post('/prospects/{prospect}/contacts', [ProspectContactController::class, 'store'])->name('prospects.contacts.store');
    Route::get('/prospects/{prospect}/contacts/{contact}/edit', [ProspectContactController::class, 'edit'])->name('prospects.contacts.edit');
    Route::put('/prospects/{prospect}/contacts/{contact}', [ProspectContactController::class, 'update'])->name('prospects.contacts.update');
    Route::delete('/prospects/{prospect}/contacts/{contact}', [ProspectContactController::class, 'destroy'])->name('prospects.contacts.destroy');

    Route::post('/prospects/{prospect}/follow-ups', [FollowUpActivityController::class, 'store'])->name('prospects.follow-ups.store');
    Route::get('/prospects/{prospect}/follow-ups/{followUpActivity}/edit', [FollowUpActivityController::class, 'edit'])->name('prospects.follow-ups.edit');
    Route::put('/prospects/{prospect}/follow-ups/{followUpActivity}', [FollowUpActivityController::class, 'update'])->name('prospects.follow-ups.update');
    Route::delete('/prospects/{prospect}/follow-ups/{followUpActivity}', [FollowUpActivityController::class, 'destroy'])->name('prospects.follow-ups.destroy');
});

Route::middleware(['auth', 'role:admin,sales,manager,finance'])->group(function () {
    Route::get('/prospects/{prospect}', [ProspectController::class, 'show'])->name('prospects.show');
    Route::get('/quotations', [QuotationController::class, 'index'])->name('quotations.index');
});

Route::middleware(['auth', 'role:admin,sales'])->group(function () {
    Route::get('/quotations/create', [QuotationController::class, 'create'])->name('quotations.create');
    Route::post('/quotations', [QuotationController::class, 'store'])->name('quotations.store');
    Route::post('/quotations/{quotation}/submit', [QuotationApprovalController::class, 'submit'])->name('quotations.submit');
});

Route::middleware(['auth', 'role:manager'])->group(function () {
    Route::post('/quotations/{quotation}/approve', [QuotationApprovalController::class, 'approve'])->name('quotations.approve');
    Route::post('/quotations/{quotation}/reject', [QuotationApprovalController::class, 'reject'])->name('quotations.reject');
});

Route::middleware(['auth', 'role:admin,sales,manager'])->group(function () {
    Route::post('/quotations/{quotation}/generate-pdf', [QuotationPdfController::class, 'generate'])->name('quotations.generate-pdf');
    Route::post('/quotations/{quotation}/mark-sent', [QuotationApprovalController::class, 'markSent'])->name('quotations.mark-sent');
});

Route::middleware(['auth', 'role:admin,sales,manager,finance'])->group(function () {
    Route::get('/quotations/{quotation}/download-pdf', [QuotationPdfController::class, 'download'])->name('quotations.download-pdf');
});

Route::middleware(['auth', 'role:admin,sales,manager,finance'])->group(function () {
    Route::get('/quotations/{quotation}', [QuotationController::class, 'show'])->name('quotations.show');
});

Route::middleware(['auth', 'role:admin,sales,manager'])->group(function () {
    Route::get('/follow-ups/today', [FollowUpActivityController::class, 'today'])->name('follow-ups.today');
    Route::get('/follow-ups/overdue', [FollowUpActivityController::class, 'overdue'])->name('follow-ups.overdue');
});

Route::middleware(['auth', 'role:admin,manager'])->group(function () {
    Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
});

require __DIR__.'/auth.php';
