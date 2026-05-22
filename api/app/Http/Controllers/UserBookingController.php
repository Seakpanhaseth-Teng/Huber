<?php

namespace App\Http\Controllers;

use App\Models\RidePurchase;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserBookingController extends Controller
{
    public function index(): View|RedirectResponse
    {
        $user = auth()->user();
        if (! $user) {
            return redirect()->route('login')->with('error', 'Please login to view your bookings.');
        }

        $bookings = RidePurchase::with(['ride.user'])
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        /** @phpstan-var view-string $view */
        $view = 'user.bookings';
        return view($view, compact('user', 'bookings'));
    }

    public function show($bookingId): View|RedirectResponse
    {
        $user = auth()->user();
        if (! $user) {
            return redirect()->route('login')->with('error', 'Please login to view booking details.');
        }

        $booking = RidePurchase::with(['ride.user'])
            ->where('id', $bookingId)
            ->where('user_id', $user->id)
            ->first();

        if (! $booking) {
            return redirect()->route('user.bookings')->with('error', 'Booking not found.');
        }

        /** @phpstan-var view-string $view */
        $view = 'user.booking-details';
        return view($view, compact('booking', 'user'));
    }

    public function printReceipt($bookingId): View|RedirectResponse
    {
        $user = auth()->user();
        if (! $user) {
            return redirect()->route('login')->with('error', 'Please login to print receipt.');
        }

        $booking = RidePurchase::with(['ride.user'])
            ->where('id', $bookingId)
            ->where('user_id', $user->id)
            ->first();

        if (! $booking) {
            return redirect()->route('user.bookings')->with('error', 'Booking not found.');
        }

        /** @phpstan-var view-string $view */
        $view = 'user.receipt';
        return view($view, compact('booking', 'user'));
    }

    // API Methods
    public function apiIndex(Request $request): JsonResponse
    {
        // Get user from token authentication
        $user = $request->user();
        if (! $user) {
            return response()->json([
                'message' => 'Please login to view your bookings.',
                'status' => 'error',
            ], 401);
        }

        $bookings = RidePurchase::with(['ride.user'])
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'message' => 'User bookings retrieved successfully',
            'status' => 'success',
            'data' => [
                'user' => $user,
                'bookings' => $bookings,
            ],
        ]);
    }

    public function apiShow(Request $request, $bookingId): JsonResponse
    {
        // Get user from token authentication
        $user = $request->user();
        if (! $user) {
            return response()->json([
                'message' => 'Please login to view booking details.',
                'status' => 'error',
            ], 401);
        }

        $booking = RidePurchase::with(['ride.user'])
            ->where('id', $bookingId)
            ->where('user_id', $user->id)
            ->first();

        if (! $booking) {
            return response()->json([
                'message' => 'Booking not found.',
                'status' => 'error',
            ], 404);
        }

        return response()->json([
            'message' => 'Booking details retrieved successfully',
            'status' => 'success',
            'data' => [
                'booking' => $booking,
                'user' => $user,
            ],
        ]);
    }

    public function apiPrintReceipt(Request $request, $bookingId): JsonResponse
    {
        // Get user from token authentication
        $user = $request->user();
        if (! $user) {
            return response()->json([
                'message' => 'Please login to print receipt.',
                'status' => 'error',
            ], 401);
        }

        $booking = RidePurchase::with(['ride.user'])
            ->where('id', $bookingId)
            ->where('user_id', $user->id)
            ->first();

        if (! $booking) {
            return response()->json([
                'message' => 'Booking not found.',
                'status' => 'error',
            ], 404);
        }

        return response()->json([
            'message' => 'Receipt data retrieved successfully',
            'status' => 'success',
            'data' => [
                'booking' => $booking,
                'user' => $user,
                'receipt_data' => [
                    'booking_id' => $booking->id,
                    'ride_details' => $booking->ride,
                    'passenger_details' => $booking->passenger_details,
                    'payment_amount' => $booking->total_amount,
                    'booking_date' => $booking->created_at,
                    'trip_type' => $booking->trip_type,
                ],
            ],
        ]);
    }
}
