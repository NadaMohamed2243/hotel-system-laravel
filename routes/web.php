<?php
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ClientReservationController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\ManagerController;

use App\Http\Controllers\Auth\RegisteredUserController;  //H
use Illuminate\Support\Facades\Auth;//H
use App\Http\Controllers\ReservationController; //H


use App\Http\Controllers\RoomController;

use App\Http\Controllers\ReceptionistController;
use App\Http\Controllers\FloorController;
Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');



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

//-----------------------------------client  //H --------------------------------------------
Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
Route::post('/register', [RegisteredUserController::class, 'store']);


Route::get('/pending-approval', function () {
    return Inertia::render('PendingApproval');
})->name('pending.approval')->middleware('auth');


//Client dashboard - only accessible by clients
Route::get('/dashboard', function () {
    $user = Auth::user();
    $user->load('client');

    if ($user->client && $user->client->status !== 'approved') {
        return redirect()->route('pending.approval');
    }

    return Inertia::render('HClient/ClientDashboard');
})->middleware(['auth'])->name('dashboard');





Route::middleware(['auth', 'verified'])->group(function () {

    // Show available rooms for reservation
    Route::get('/client/make-reservation', [ReservationController::class, 'showAvailableRooms'])
        ->name('client.makeReservation');

    // Show reservation form for a specific room
    Route::get('/client/make-reservation/{roomId}', [ReservationController::class, 'showReservationForm'])
        ->name('client.showReservationForm');

    // Store reservation
    Route::post('/client/reserve', [ReservationController::class, 'storeReservation'])
        ->name('client.reserve');

    // Cancel reservation
    Route::post('/client/cancel-reservation', [ReservationController::class, 'cancelReservation'])
        ->name('client.cancelReservation');

    // Show client's reservations
    Route::get('/client/my-reservations', [ReservationController::class, 'myReservations'])
        ->name('client.reservations');

    Route::get('/client/make-reservation/{roomId}/payment', [ReservationController::class, 'showPaymentForm'])
    ->name('client.payment');

    Route::post('/client/payment/{roomId}', [ReservationController::class, 'processPayment']);


        Route::prefix('client')->middleware('auth')->group(function() {
            //
            Route::post('/create-payment-intent', [ReservationController::class, 'createPaymentIntent'])->name('client.createPaymentIntent');
        });

});






require __DIR__.'/settings.php';
require __DIR__.'/auth.php';



// Managage Receptionists

Route::prefix('admin')->middleware(['auth', 'verified','permission:manage receptionists'])->group(function () {
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

Route::prefix('admin/floors')
    ->middleware(['auth', 'verified', 'permission:manage floors'])
    ->group(function () {
        Route::get('/', [FloorController::class, 'index'])->name('admin.floors.index');
        Route::get('/create', [FloorController::class, 'create'])->name('admin.floors.create');
        Route::post('/', [FloorController::class, 'store'])->name('admin.floors.store');
        Route::get('/{floor}/edit', [FloorController::class, 'edit'])->name('admin.floors.edit');
        Route::put('/{floor}', [FloorController::class, 'update'])->name('admin.floors.update');
        Route::delete('/{floor}', [FloorController::class, 'destroy'])->name('admin.floors.destroy');
    });


// Manager-specific routes
Route::prefix('manager')->middleware(['auth'])->group(function () {

    Route::get('/dashboard', function () {
        // Redirect managers to admin dashboard
        return Inertia::render('AdminDashboard');
    })->name('manager.dashboard');


    // Receptionist Management
    Route::prefix('receptionists')
    ->middleware(['auth', 'verified', 'role:manager','permission:manage receptionists'])
    ->group(function () {
        Route::get('/', [ReceptionistController::class, 'index'])->name('manager.receptionists.index');
        Route::get('/create', [ReceptionistController::class, 'create'])->name('manager.receptionists.create');
        Route::post('/', [ReceptionistController::class, 'store'])->name('manager.receptionists.store');
        Route::get('/{receptionist}/edit', [ReceptionistController::class, 'edit'])->name('manager.receptionists.edit');
        Route::put('/{receptionist}', [ReceptionistController::class, 'update'])->name('manager.receptionists.update');
        Route::delete('/{receptionist}', [ReceptionistController::class, 'destroy'])->name('manager.receptionists.destroy');
    });


});

// Manager-specific floor routes
Route::prefix('manager/floors')
    ->middleware(['auth', 'verified', 'role:manager','permission:manage floors'])
    ->group(function () {
        Route::get('/', [FloorController::class, 'index'])->name('manager.floors.index');
        Route::get('/create', [FloorController::class, 'create'])->name('manager.floors.create');
        Route::post('/', [FloorController::class, 'store'])->name('manager.floors.store');
        Route::get('/{floor}/edit', [FloorController::class, 'edit'])->name('manager.floors.edit');
        Route::put('/{floor}', [FloorController::class, 'update'])->name('manager.floors.update');
        Route::delete('/{floor}', [FloorController::class, 'destroy'])->name('manager.floors.destroy');
});


Route::prefix('admin')->middleware(['auth', 'verified', 'permission:manage reservations'])->group(function () {
    Route::get('/clients/reservations', [ReservationController::class, 'index'])->name('admin.clientReservations');
});
Route::prefix('manager')->middleware(['auth', 'verified', 'permission:manage reservations'])->group(function () {
    Route::get('/clients/reservations', [ReservationController::class, 'index'])->name('manager.clientReservations');
});
