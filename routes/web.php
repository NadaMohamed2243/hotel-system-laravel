<?php
use App\Http\Controllers\ClientController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\ManagerController;
use App\Http\Controllers\RoomController;


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
//-----------------------------------Rooms--------------------------------------------
// Room routes for Admin and Manager
foreach (['admin', 'manager'] as $role) {
    Route::prefix("$role/rooms")
        ->middleware(['auth', 'verified', 'permission:manage rooms'])
        ->name("$role.rooms.")
        ->group(function () {
            Route::get('/', [RoomController::class, 'index'])->name('index');
            Route::post('/', [RoomController::class, 'store'])->name('store');
            Route::delete('/{room}', [RoomController::class, 'destroy'])->name('destroy');
            Route::get('/{room}/edit', [RoomController::class, 'edit'])->name('edit');
            Route::put('/{room}', [RoomController::class, 'update'])->name('update');
        });
}
//-----------------------------------Clients--------------------------------------------
// Client routes for Admin and Manager
foreach (['admin', 'manager'] as $role) {
    Route::prefix("$role/clients")
        ->middleware(['auth', 'verified', 'permission:manage clients'])
        ->name("$role.clients.")
        ->group(function () {
            Route::get('/', [ClientController::class, 'index'])->name('index');
            Route::post('/', [ClientController::class, 'store'])->name('store');
            Route::delete('/{client}', [ClientController::class, 'destroy'])->name('destroy');
            Route::put('/{client}', [ClientController::class, 'updateClient'])->name('updateClient');
            Route::post('/validate-step1', [ClientController::class, 'validateStep1'])->name('validateStep1');
        });
}


require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
