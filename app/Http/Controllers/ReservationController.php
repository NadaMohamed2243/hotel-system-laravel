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
        Log::info('Received reservation request:', $request->all());

        $room = Room::findOrFail($request->room_id);

        if ($room->is_reserved) {
            return response()->json([
                'error' => 'This room is already reserved'
            ], 422);
        }

        if ($request->accompany_number > $room->capacity) {
            return response()->json([
                'error' => 'Exceeds room capacity'
            ], 422);
        }

        //reserve the room
        $reservation = Reservation::create([
            'client_id' => Auth::user()->client->id,
            'room_id' => $room->id,
            'accompany_number' => $request->accompany_number,
            'paid_price' => $room->price,
            'reserved_at' => now(),
        ]);

        Log::info('Reservation stored successfully:', [
            'reservation_id' => $reservation->id,
            'client_id' => $reservation->client_id,
            'room_id' => $reservation->room_id
        ]);

        // Return JSON response with redirect URL
        return response()->json([
            'success' => true,
            'redirect' => route('client.payment', ['roomId' => $room->id])
        ]);
    }

    // Process payment using Stripe
    public function showPaymentForm($roomId)
    {
        $room = Room::findOrFail($roomId);

        if (!$room) {
            return redirect()->route('client.reservations')->with('error', 'Room not found');
        }

        // Create payment intent
        $paymentIntent = $this->createPaymentIntent($room->price);

        // Check if there was an error creating the payment intent
        if (isset($paymentIntent['error'])) {
            return back()->withErrors(['payment' => $paymentIntent['error']]);
        }

        return Inertia::render('HClient/PaymentForm', [
            'roomId' => $room->id,
            'clientSecret' => $paymentIntent['clientSecret'],
            'amount' => $room->price
        ]);
    }

    // to create a payment intent
    public function createPaymentIntent($amount)
    {
        if (!env('STRIPE_SECRET_KEY')) {
            Log::error('Stripe secret key is not configured');
            return ['error' => 'Payment system is not properly configured'];
        }

        Stripe::setApiKey(env('STRIPE_SECRET_KEY'));

        try {
            $paymentIntent = PaymentIntent::create([
                'amount' => (int)($amount * 100), // amount in cents, ensure it's an integer
                'currency' => 'usd',
            ]);

            if (!$paymentIntent || !$paymentIntent->client_secret) {
                Log::error('Failed to create payment intent: No client secret received');
                return ['error' => 'Failed to initialize payment'];
            }

            return ['clientSecret' => $paymentIntent->client_secret];
        } catch (\Exception $e) {
            Log::error('Stripe payment intent creation failed: ' . $e->getMessage());
            return ['error' => 'Payment initialization failed: ' . $e->getMessage()];
        }
    }

    // Handle successful payment
    public function paymentSuccess($roomId)
    {
        try {
            $room = Room::findOrFail($roomId);
            
            // Find the reservation
            $reservation = Reservation::where('room_id', $roomId)
                ->where('client_id', Auth::user()->client->id)
                ->latest()
                ->firstOrFail();

            // Update reservation status
            $reservation->update([
                'status' => 'paid',
                'payment_confirmed_at' => now()
            ]);

            // Update room status
            $room->update(['is_reserved' => true]);

            return Inertia::render('HClient/PaymentSuccess', [
                'reservation' => [
                    'id' => $reservation->id,
                    'room_number' => $room->number,
                    'paid_amount' => $reservation->paid_price,
                    'booking_date' => $reservation->created_at->format('Y-m-d H:i:s')
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Payment success handling failed: ' . $e->getMessage());
            return redirect()->route('client.myReservations')
                ->with('error', 'There was an issue confirming your payment. Please contact support.');
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
    }
}
