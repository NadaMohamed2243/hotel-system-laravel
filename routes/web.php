<?php
use App\Http\Controllers\ClientController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\ManagerController;


Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

// Client dashboard
Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified', 'client.only'])->name('dashboard');

// Admin dashboard
Route::get('admin/dashboard', function () {
    return Inertia::render('AdminDashboard');
})->middleware(['auth', 'verified'])->name('admin.dashboard');

// Manager routes - using permission middleware
Route::prefix('admin/managers')
    ->middleware(['auth', 'verified', 'permission:manage managers'])
    ->group(function () {
        Route::get('/', [ManagerController::class, 'index'])->name('managers.index');
        Route::post('/', [ManagerController::class, 'store'])->name('managers.store');
        Route::delete('/{user}', [ManagerController::class, 'destroy'])->name('managers.destroy');
        Route::get('/{user}/edit', [ManagerController::class, 'edit'])->name('managers.edit');
        Route::put('/{user}', [ManagerController::class, 'update'])->name('managers.update');
    });



//-----------------------------------receptionist--------------------------------------------
Route::prefix('dashboard/receptionist')->middleware(['auth', 'verified'])->group(function () {
    Route::get('/', function () {
        return Inertia::render('AdminDashboard');
    })->name('receptionist.dashboard');

    Route::get('/clients/pending', [ClientController::class, 'pendingClients'])->name('receptionist.pendingClients');
    Route::get('/clients/approved', [ClientController::class, 'approvedClients'])->name('receptionist.approvedClients');
    Route::get('/clients/reservations', [ClientController::class, 'clientReservations'])->name('receptionist.clientReservations');

    Route::PATCH('/clients/{client}', [ClientController::class, 'update'])->name('receptionist.update');
    Route::delete('/clients/delete/{id}', [ClientController::class, 'delete'])->name('receptionist.unapproveClient');
});


require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
