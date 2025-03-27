<?php

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\RoomController;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;
use App\Models\Room;
use App\Http\Controllers\HClientAuthController;
use App\Http\Controllers\ReservationController;



Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->get('/rooms', [RoomController::class, 'index']);
Route::middleware('auth:sanctum')->get('/rooms/{id}', [RoomController::class, 'show']);
Route::middleware('auth:sanctum')->post('/rooms', [RoomController::class, 'store']);


Route::post('/sanctum/token', function (Request $request) {
    $request->validate([
        'email' => 'required|email',
        'password' => 'required',
        'device_name' => 'required'
    ]);

    $user = User::where('email', $request->email)->first();

    if (! $user || ! Hash::check($request->password, $user->password)) {
        throw ValidationException::withMessages([
            'email' => ['The provided credentials are incorrect.'],
        ]);
    }

    return $user->createToken($request->device_name)->plainTextToken;
});



Route::post('/register', [HClientAuthController::class, 'register']);


Route::middleware('auth:sanctum')->group(function () {
   // Route::get('/client/reservations', [HClientReservationController::class, 'myReservations']);
});


Route::post('/create-payment-intent', [ReservationController::class, 'createPaymentIntent']);

