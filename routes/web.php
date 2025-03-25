<?php
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ClientReservationController;
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

    Route::get('/clients/reservations', [ClientReservationController::class, 'index'])->name('receptionist.clientReservations.index');

    Route::PATCH('/clients/{client}', [ClientController::class, 'update'])->name('receptionist.update');
    Route::delete('/clients/delete/{id}', [ClientController::class, 'delete'])->name('receptionist.unapproveClient');
});
//-----------------------------------Rooms--------------------------------------------
//Room routes for admin
Route::prefix('admin/rooms')->
    middleware(['auth', 'verified','permission:manage rooms'])->
    group(function () {
        Route::get('/', [RoomController::class, 'index'])->name('rooms.index');
        Route::post('/', [RoomController::class, 'store'])->name('rooms.store');
        Route::delete('/{room}', [RoomController::class, 'destroy'])->name('rooms.destroy');
        Route::get('/{room}/edit', [RoomController::class, 'edit'])->name('rooms.edit');
        Route::put('/{room}', [RoomController::class, 'update'])->name('rooms.update');
});
//Room routes for manager
Route::prefix('manager/rooms')->
    middleware(['auth', 'verified','permission:manage rooms'])->
    group(function () {
        Route::get('/', [RoomController::class, 'index'])->name('rooms.index');
        Route::post('/', [RoomController::class, 'store'])->name('rooms.store');
        Route::delete('/{room}', [RoomController::class, 'destroy'])->name('rooms.destroy');
        Route::get('/{room}/edit', [RoomController::class, 'edit'])->name('rooms.edit');
        Route::put('/{room}', [RoomController::class, 'update'])->name('rooms.update');
});


require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
