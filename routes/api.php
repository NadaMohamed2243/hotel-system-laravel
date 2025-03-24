<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HClientAuthController;



Route::post('/register', [HClientAuthController::class, 'register']);


Route::middleware('auth:sanctum')->group(function () {
   // Route::get('/client/reservations', [HClientReservationController::class, 'myReservations']);
});
