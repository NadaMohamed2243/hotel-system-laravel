<?php
use App\Http\Controllers\ClientController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\ManagerController;
use App\Http\Controllers\Auth\RegisteredUserController;  //H
use Illuminate\Support\Facades\Auth;//H
use App\Http\Controllers\ReservationController; //H



Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');



// Admin dashboard - requires access admin panel permission
Route::get('admin/dashboard', function () {
    return Inertia::render('AdminDashboard');
})->middleware(['auth', 'verified'])->name('admin.dashboard');

// Manager routes - already has permission checks in the controller
Route::prefix('admin/managers')->middleware(['auth', 'verified'])->group(function () {
    Route::get('/', [ManagerController::class, 'index'])->name('managers.index');
    Route::post('/', [ManagerController::class, 'store'])->name('managers.store');
    Route::delete('/{user}', [ManagerController::class, 'destroy'])->name('managers.destroy');
    Route::get('/{user}/edit', [ManagerController::class, 'edit'])->name('managers.edit');
    Route::put('/{user}', [ManagerController::class, 'update'])->name('managers.update');
});

// These routes are now handled in the routes group above
// Route::delete('/admin/managers/{user}', [ManagerController::class, 'destroy'])->name('managers.destroy');
// Route::get('/admin/managers/{user}/edit', [ManagerController::class, 'edit'])->name('managers.edit');
// Route::put('/admin/managers/{user}', [ManagerController::class, 'update'])->name('managers.update');



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

//-----------------------------------client  //H --------------------------------------------
Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
Route::post('/register', [RegisteredUserController::class, 'store']);


Route::get('/pending-approval', function () {
    return Inertia::render('PendingApproval');
})->name('pending.approval')->middleware('auth');


// Client dashboard - only accessible by clients
Route::get('/dashboard', function () {
    $user = Auth::user();
    $user->load('client');

    if ($user->client && $user->client->status !== 'approved') {
        return redirect()->route('pending.approval');
    }

    return Inertia::render('HClient/ClientDashboard');
})->middleware(['auth'])->name('dashboard');



// Client reservations
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/my-reservations', [ReservationController::class, 'myReservations']);
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/client/reservations', function () {
        return Inertia::render('HClient/MyReservations');
    })->name('client.reservations');
});



require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
