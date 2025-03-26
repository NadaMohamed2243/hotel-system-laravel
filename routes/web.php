<?php
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ClientReservationController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\ManagerController;
use App\Http\Controllers\RoomController;

use App\Http\Controllers\ReceptionistController;
use App\Http\Controllers\FloorController;
use App\Http\Controllers\ReservationController;
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

    Route::get('/clients/reservations', [ClientReservationController::class, 'index'])->name('receptionist.clientReservations');

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



// Managage Receptionists

Route::prefix('admin')->middleware(['auth', 'verified'])->group(function () {
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


//Floor Management

Route::prefix('admin')->middleware(['auth', 'verified'])->group(function () {

Route::prefix('floors')->group(function () {
    Route::get('/', [FloorController::class, 'index'])->name('admin.floors.index');
    Route::get('/create', [FloorController::class, 'create'])->name('admin.floors.create');
    Route::post('/', [FloorController::class, 'store'])->name('admin.floors.store');
    Route::get('/{floor}/edit', [FloorController::class, 'edit'])->name('admin.floors.edit');
    Route::put('/{floor}', [FloorController::class, 'update'])->name('admin.floors.update');
    Route::delete('/{floor}', [FloorController::class, 'destroy'])->name('admin.floors.destroy');
});

});



// Manager-specific routes
Route::prefix('manager')->middleware(['auth'])->group(function () {

    Route::get('/dashboard', function () {
        // Redirect managers to admin dashboard
        return Inertia::render('AdminDashboard');
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


Route::prefix('admin')->middleware(['auth', 'verified', 'permission:manage reservations'])->group(function () {
    Route::get('/clients/reservations', [ReservationController::class, 'index'])->name('admin.clientReservations');
});
Route::prefix('manager')->middleware(['auth', 'verified', 'permission:manage reservations'])->group(function () {
    Route::get('/clients/reservations', [ReservationController::class, 'index'])->name('manager.clientReservations');
});
