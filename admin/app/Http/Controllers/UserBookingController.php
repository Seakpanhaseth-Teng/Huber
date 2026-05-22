<?php

namespace App\Http\Controllers;

use App\Models\RidePurchase;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class UserBookingController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();

        $bookings = RidePurchase::with(['ride.user'])
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('user.bookings', compact('user', 'bookings'));
    }

    public function show($bookingId): View|RedirectResponse
    {
        $user = auth()->user();

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

        $booking = RidePurchase::with(['ride.user'])
            ->where('id', $bookingId)
            ->where('user_id', $user->id)
            ->first();

        if (! $booking) {
            return redirect()->route('user.bookings')->with('error', 'Booking not found.');
        }

        return view('user.receipt', compact('booking', 'user'));
    }
}
