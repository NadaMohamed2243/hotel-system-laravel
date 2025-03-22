<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\ReceptionistController;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';



// Managage Receptionists

Route::prefix('admin')->middleware(['auth', 'role:admin'])->group(function () {
    // Admin receptionist management
    Route::prefix('receptionists')->group(function () {
        Route::get('/', [ReceptionistController::class, 'index'])->name('admin.receptionists.index');
        Route::get('/create', [ReceptionistController::class, 'create'])->name('admin.receptionists.create');
        Route::post('/', [ReceptionistController::class, 'store'])->name('admin.receptionists.store');
        Route::get('/{receptionist}/edit', [ReceptionistController::class, 'edit'])->name('admin.receptionists.edit');
        Route::put('/{receptionist}', [ReceptionistController::class, 'update'])->name('admin.receptionists.update');
        Route::delete('/{receptionist}', [ReceptionistController::class, 'destroy'])->name('admin.receptionists.destroy');
        Route::post('/{receptionist}/ban', [ReceptionistController::class, 'ban'])->name('admin.receptionists.ban');
        Route::post('/{receptionist}/unban', [ReceptionistController::class, 'unban'])->name('admin.receptionists.unban');
    });
});


// Manager-specific routes
Route::prefix('manager')->middleware(['auth'])->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Manager/Dashboard', [
            'pageTitle' => 'Manager Dashboard',
        ]);
    })->name('manager.dashboard');

    // Receptionist Management
    Route::prefix('receptionists')->group(function () {
        Route::get('/', [ReceptionistController::class, 'index'])->name('manager.receptionists.index');
        Route::get('/create', [ReceptionistController::class, 'create'])->name('manager.receptionists.create');
        Route::post('/', [ReceptionistController::class, 'store'])->name('manager.receptionists.store');
        Route::get('/{receptionist}/edit', [ReceptionistController::class, 'edit'])->name('manager.receptionists.edit');
        Route::put('/{receptionist}', [ReceptionistController::class, 'update'])->name('manager.receptionists.update');
        Route::delete('/{receptionist}', [ReceptionistController::class, 'destroy'])->name('manager.receptionists.destroy');
    });


});
