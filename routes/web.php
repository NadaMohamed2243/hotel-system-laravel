<?php
use App\Http\Controllers\ClientController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;


Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');



//-----------------------------------receptionist--------------------------------------------
Route::get('dashboard/receptionist', function () {
    return Inertia::render('ReceptionistDashboard');
})->middleware(middleware: ['auth', 'verified'])->name('ReceptionistDashboard');


Route::get('dashboard/receptionist/clients/pending', [ClientController::class, 'pendingClients'])
->middleware(middleware: ['auth', 'verified'])->name('receptionist.pendingClients');


Route::get('dashboard/receptionist/clients/approved', [ClientController::class, 'approvedClients'])
->middleware(['auth', 'verified'])->name('receptionist.approvedClients');


// Route::get('dashboard/receptionist/clients/reservations', function () {
//     return Inertia::render('Receptionist/clientReservations');
// })->middleware(['auth', 'verified'])->name('receptionist.clientReservations');

Route::get('dashboard/receptionist/clients/reservations', [ClientController::class, 'clientReservations'])
->middleware(['auth', 'verified'])->name('receptionist.clientReservations');



Route::post('dashboard/receptionist/clients/approve/{id}', [ClientController::class, 'approveClient'])
->middleware(['auth', 'verified'])->name('receptionist.approveClient');


require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
