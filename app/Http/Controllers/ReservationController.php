<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;
use App\Models\Reservation;
use App\Models\Room;
use Stripe\Stripe;
use Stripe\Charge;
use Stripe\PaymentIntent;

class ReservationController extends Controller
{

    public function index(Request $request)
     {
         try {
             $validated = $request->validate([
                 'page' => 'sometimes|integer|min:1',
                 'pageSize' => 'sometimes|integer|min:1|max:100',
             ]);

             $page = $validated['page'] ?? 1;
             $pageSize = $validated['pageSize'] ?? 5;

             $reservations = Reservation::whereHas('client')
                 ->with(['client.user:id,name,email', 'room:id,number'])
                 ->orderBy('created_at', 'desc')
                 ->paginate($pageSize, ['*'], 'page', $page);

             return Inertia::render('Managers/clientReservations/index', [
                 'reservations' => $reservations->items(),
                 'pagination' => [ // Make sure this matches your frontend expectation
                     'page' => $reservations->currentPage(),
                     'pageSize' => $reservations->perPage(),
                     'total' => $reservations->total(),
                 ],
             ]);

         } catch (\Exception $e) {
             Log::error('ClientReservationController error: ' . $e->getMessage());
             return back()->with('error', 'Failed to load reservations');
         }
     }

    // Display the user's reservations
    public function myReservations()
    {
        $user = Auth::user();

        if (!$user->client) {
            abort(403, 'Unauthorized');
        }

        $reservations = Reservation::where('client_id', $user->client->id)
            ->select('id', 'room_id', 'accompany_number', 'paid_price')
            ->get();

        return Inertia::render('HClient/MyReservations', [
            'reservations' => $reservations
        ]);
    }

    // Show available rooms
    public function showAvailableRooms()
    {
        $availableRooms = Room::where('is_reserved', false)
        ->select('id', 'number', 'capacity', 'price', 'is_reserved')
        ->get();

        // dd($availableRooms); // 🔍

        return Inertia::render('HClient/MakeReservation', [
            'rooms' => $availableRooms
        ]);
    }

    // Show reservation form for a specific room
    public function showReservationForm($roomId)
    {
        $room = Room::findOrFail($roomId);
        return Inertia::render('HClient/ReservationForm', ['room' => $room]);
    }

    // Store a new reservation
    public function storeReservation(Request $request)
    {
        //
        Log::info('Received reservation request:', $request->all());

        $room = Room::findOrFail($request->room_id);

        if ($room->is_reserved) {
            return Inertia::render('ErrorPage', ['message' => 'This room is already reserved']);
        }

        if ($request->accompany_number > $room->capacity) {
            return back()->withErrors(['accompany_number' => 'Exceeds room capacity']);
        }

        //reserve the room
        $reservation = Reservation::create([
            'client_id' => Auth::user()->client->id,
            'room_id' => $room->id,
            'accompany_number' => $request->accompany_number,
            'paid_price' => $room->price,
            'reserved_at' => now(),
        ]);

        //add reservation message
        Log::info('Reservation stored successfully:', $reservation);

        //create a payment intent
        $paymentIntent = $this->createPaymentIntent($room->price);

        //
        return Inertia::render('PaymentPage', [
            'roomId' => $room->id,
            'clientSecret' => $paymentIntent['clientSecret'],
        ]);
            }








      // Process payment using Stripe
      public function showPaymentForm($roomId)
      {
          $room = Room::find($roomId);

          if (!$room) {
              return redirect()->route('client.reservations')->with('error', 'Room not found');
          }

          return inertia('HClient/PaymentForm', ['room' => $room]);
      }


      //
public function processPayment(Request $request, $roomId)
{
    // Validate the request
    $request->validate([
        'payment_method_id' => 'required|string',
    ]);

    $room = Room::findOrFail($roomId);

    //stripe to process payment
    try {
        Stripe::setApiKey(env('STRIPE_SECRET_KEY'));


        $paymentIntent = PaymentIntent::create([
            'amount' => $room->price * 100, //price in cents
            'currency' => 'usd',
            'payment_method' => $request->payment_method_id,
            'confirm' => true,
        ]);

        // if payment is successful, update the reservation status to "paid"
        $reservation = Reservation::where('room_id', $roomId)
            ->where('client_id', Auth::id())
            ->first();

        if (!$reservation) {
            return back()->withErrors(['reservation_error' => 'Reservation not found']);
        }
        // update the reservation status to "paid"
        $reservation->status = 'paid';
        $reservation->save();

        return redirect()->route('client.myReservations')->with('success', 'Payment successful, reservation confirmed!');
    } catch (\Exception $e) {
        return back()->withErrors(['payment_error' => 'Payment failed: ' . $e->getMessage()]);
    }
}


      // to create a payment intent
      public function createPaymentIntent($amount)
      {
          Stripe::setApiKey(env('STRIPE_SECRET_KEY'));

          try {
              $paymentIntent = PaymentIntent::create([
                  'amount' => $amount * 100, // amount in cents
                  'currency' => 'usd',
              ]);

              return ['clientSecret' => $paymentIntent->client_secret];
          } catch (\Exception $e) {
              return ['error' => $e->getMessage()];
          }
      }

    // Cancel a reservation
    public function cancelReservation(Request $request)
    {
        $request->validate([
            'reservation_id' => 'required|exists:reservations,id',
        ]);

        $reservation = Reservation::findOrFail($request->reservation_id);
        $reservation->room->update(['is_reserved' => false]);
        $reservation->delete();

        return redirect()->route('client.reservations')->with('success', 'Reservation canceled successfully.');

// use App\Models\Reservation;
// use Inertia\Inertia;
// use Illuminate\Support\Facades\Log;

// class ReservationController extends Controller
// {
//     public function index(Request $request)
//     {
//         try {
//             $validated = $request->validate([
//                 'page' => 'sometimes|integer|min:1',
//                 'pageSize' => 'sometimes|integer|min:1|max:100',
//             ]);

//             $page = $validated['page'] ?? 1;
//             $pageSize = $validated['pageSize'] ?? 5;

//             $reservations = Reservation::whereHas('client')
//                 ->with(['client.user:id,name,email', 'room:id,number'])
//                 ->orderBy('created_at', 'desc')
//                 ->paginate($pageSize, ['*'], 'page', $page);

//             return Inertia::render('Managers/clientReservations/index', [
//                 'reservations' => $reservations->items(),
//                 'pagination' => [ // Make sure this matches your frontend expectation
//                     'page' => $reservations->currentPage(),
//                     'pageSize' => $reservations->perPage(),
//                     'total' => $reservations->total(),
//                 ],
//             ]);

//         } catch (\Exception $e) {
//             Log::error('ClientReservationController error: ' . $e->getMessage());
//             return back()->with('error', 'Failed to load reservations');
//         }

    }
}
