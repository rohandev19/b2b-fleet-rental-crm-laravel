<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProspectContactController;
use App\Http\Controllers\ProspectController;
use App\Http\Controllers\UserController;
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
});

Route::middleware(['auth', 'role:admin,sales,manager,finance'])->group(function () {
    Route::get('/prospects/{prospect}', [ProspectController::class, 'show'])->name('prospects.show');
});

require __DIR__.'/auth.php';
