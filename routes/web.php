<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\ManagerController;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified', 'client.only'])->name('dashboard');

Route::get('admin/dashboard', function () {
    return Inertia::render('AdminDashboard');
})->middleware(['auth', 'verified'])->name('admin.dashboard');

// Manager routes
Route::prefix('admin/managers')->middleware(['auth', 'verified'])->group(function () {
    Route::get('/', [ManagerController::class, 'index'])->name('managers.index');
    Route::post('/', [ManagerController::class, 'store'])->name('managers.store');
});

Route::delete('/admin/managers/{user}', [ManagerController::class, 'destroy'])->name('managers.destroy');
Route::get('/admin/managers/{user}/edit', [ManagerController::class, 'edit'])->name('managers.edit');
Route::put('/admin/managers/{user}', [ManagerController::class, 'update'])->name('managers.update');

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
