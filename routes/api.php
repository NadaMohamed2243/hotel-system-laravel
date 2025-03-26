<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HClientAuthController;
use App\Http\Controllers\ReservationController;




Route::post('/register', [HClientAuthController::class, 'register']);


Route::middleware('auth:sanctum')->group(function () {
   // Route::get('/client/reservations', [HClientReservationController::class, 'myReservations']);
});


Route::post('/create-payment-intent', [ReservationController::class, 'createPaymentIntent']);

