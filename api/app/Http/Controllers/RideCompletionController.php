<?php

namespace App\Http\Controllers;

use App\Models\Ride;
use App\Models\RideReview;
use App\Services\DriverStatsService;
use App\Services\ReviewService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class RideCompletionController extends Controller
{
    protected $reviewService;

    protected $driverStatsService;

    public function __construct()
    {
        $this->reviewService = app(ReviewService::class);
        $this->driverStatsService = app(DriverStatsService::class);
    }

    private function updateTripStatus(Ride $ride, string $tripType, string $status): void
    {
        if ($tripType === 'return') {
            $ride->return_completion_status = $status;
            if ($status === 'completed') {
                $ride->return_completed_at = now();
            }
        } else {
            $ride->go_completion_status = $status;
            if ($status === 'completed') {
                $ride->go_completed_at = now();
            }
        }
        $ride->save();
    }

    public function markAsOngoing(Request $request, $rideId, $tripType = 'go'): RedirectResponse
    {
        $user = $this->getWebUser();
        if (! $user) {
            return $this->webRedirectLogin();
        }

        $ride = Ride::where('id', $rideId)->where('user_id', $user->id)->first();
        if (! $ride) {
            return redirect()->route('driver.ride.management')->with('error', 'Ride not found or access denied.');
        }

        try {
            DB::beginTransaction();
            $this->updateTripStatus($ride, $tripType, 'ongoing');
            DB::commit();

            return redirect()->route('driver.ride.customers', ['ride' => $rideId, 'tripType' => $tripType])
                ->with('success', 'Ride marked as ongoing successfully!');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Failed to mark ride as ongoing. Please try again.');
        }
    }

    public function markAsCompleted(Request $request, $rideId, $tripType = 'go'): RedirectResponse
    {
        $user = $this->getWebUser();
        if (! $user) {
            return $this->webRedirectLogin();
        }

        $ride = Ride::where('id', $rideId)->where('user_id', $user->id)->first();
        if (! $ride) {
            return redirect()->route('driver.ride.management')->with('error', 'Ride not found or access denied.');
        }

        try {
            DB::beginTransaction();
            $this->updateTripStatus($ride, $tripType, 'completed');
            DB::commit();

            return redirect()->route('driver.ride.customers', ['ride' => $rideId, 'tripType' => $tripType])
                ->with('success', 'Ride marked as completed successfully!');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Failed to mark ride as completed. Please try again.');
        }
    }

    public function showReviewForm($bookingId, $tripType = 'go'): View|RedirectResponse
    {
        $user = $this->getWebUser();
        if (! $user) {
            return $this->webRedirectLogin();
        }

        $booking = $this->reviewService->getBookingWithOwnershipCheck($bookingId, $user->id);
        if (! $booking) {
            return redirect()->route('user.bookings')->with('error', 'Booking not found.');
        }

        $rideStatus = $this->reviewService->getRideStatus($booking, $tripType);
        if ($rideStatus === 'pending') {
            return redirect()->route('user.bookings')->with('error', 'This ride has not started yet. You can review once the ride is completed.');
        }
        if ($rideStatus === 'ongoing') {
            return redirect()->route('user.bookings')->with('error', 'This ride is currently ongoing. You can review once the driver marks it as completed.');
        }

        if ($this->reviewService->getExistingReview($bookingId, $tripType)) {
            return redirect()->route('user.bookings')->with('error', 'You have already reviewed this ride.');
        }

        /** @phpstan-var view-string $view */
        $view = 'user.review-form';
        return view($view, compact('booking', 'tripType'));
    }

    public function submitReview(Request $request, $bookingId, $tripType = 'go'): RedirectResponse
    {
        $user = $this->getWebUser();
        if (! $user) {
            return $this->webRedirectLogin();
        }

        $booking = $this->reviewService->getBookingWithOwnershipCheck($bookingId, $user->id);
        if (! $booking) {
            return redirect()->route('user.bookings')->with('error', 'Booking not found.');
        }

        $request->validate([
            'overall_rating' => 'required|integer|min:1|max:5',
            'driver_rating' => 'required|integer|min:1|max:5',
            'vehicle_rating' => 'required|integer|min:1|max:5',
            'punctuality_rating' => 'required|integer|min:1|max:5',
            'safety_rating' => 'required|integer|min:1|max:5',
            'comfort_rating' => 'required|integer|min:1|max:5',
            'review_text' => 'nullable|string|max:1000',
        ]);

        if ($this->reviewService->getExistingReview($bookingId, $tripType)) {
            return redirect()->route('user.bookings')->with('error', 'You have already reviewed this ride.');
        }

        try {
            $this->reviewService->createReview($booking, $user->id, $tripType, [
                'overall_rating' => $request->overall_rating,
                'driver_rating' => $request->driver_rating,
                'vehicle_rating' => $request->vehicle_rating,
                'punctuality_rating' => $request->punctuality_rating,
                'safety_rating' => $request->safety_rating,
                'comfort_rating' => $request->comfort_rating,
            ], $request->review_text);

            return redirect()->route('user.bookings')->with('success', 'Thank you for your review!');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to submit review. Please try again.');
        }
    }

    public function viewRideReviews($rideId): View|RedirectResponse
    {
        $user = $this->getWebUser();
        if (! $user) {
            return $this->webRedirectLogin();
        }

        $ride = Ride::with(['reviews.user', 'reviews.ridePurchase'])
            ->where('id', $rideId)
            ->where('user_id', $user->id)
            ->first();

        if (! $ride) {
            return redirect()->route('driver.ride.management')->with('error', 'Ride not found or access denied.');
        }

        /** @phpstan-var view-string $view */
        $view = 'ride-management.reviews';
        return view($view, compact('ride'));
    }

    public function viewAllReviews(): View|RedirectResponse
    {
        $user = $this->getWebUser();
        if (! $user) {
            return $this->webRedirectLogin();
        }

        $stats = $this->driverStatsService->getReviewsStats($user);

        /** @phpstan-var view-string $view */
        $view = 'driver.all-reviews';
        return view($view, [
            'reviews' => $stats['reviews'],
            'totalReviews' => $stats['total_reviews'],
            'averageOverallRating' => $stats['average_overall_rating'],
            'averageDriverRating' => $stats['average_driver_rating'],
            'averageVehicleRating' => $stats['average_vehicle_rating'],
            'ratingDistribution' => $stats['rating_distribution'],
        ]);
    }

    // API Methods
    public function apiMarkAsOngoing(Request $request, $rideId, $tripType = 'go'): JsonResponse
    {
        $user = $this->getApiUser($request);
        if (! $user) {
            return $this->jsonError('Please login to update ride status.', 401);
        }

        $ride = Ride::where('id', $rideId)->where('user_id', $user->id)->first();
        if (! $ride) {
            return $this->jsonError('Ride not found or access denied.', 404);
        }

        try {
            DB::beginTransaction();
            $this->updateTripStatus($ride, $tripType, 'ongoing');
            DB::commit();

            return $this->jsonSuccess('Ride marked as ongoing successfully!', [
                'ride' => $ride,
                'trip_type' => $tripType,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return $this->jsonError('Failed to mark ride as ongoing. Please try again.', 500);
        }
    }

    public function apiMarkAsCompleted(Request $request, $rideId, $tripType = 'go'): JsonResponse
    {
        $user = $this->getApiUser($request);
        if (! $user) {
            return $this->jsonError('Please login to complete rides.', 401);
        }

        $ride = Ride::where('id', $rideId)->where('user_id', $user->id)->first();
        if (! $ride) {
            return $this->jsonError('Ride not found or access denied.', 404);
        }

        try {
            DB::beginTransaction();
            $this->updateTripStatus($ride, $tripType, 'completed');
            DB::commit();

            return $this->jsonSuccess('Ride marked as completed successfully!', [
                'ride' => $ride,
                'trip_type' => $tripType,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return $this->jsonError('Failed to mark ride as completed. Please try again.', 500);
        }
    }

    public function apiShowReviewForm(Request $request, $bookingId, $tripType = 'go'): JsonResponse
    {
        $user = $this->getApiUser($request);
        if (! $user) {
            return $this->jsonError('Please login to review rides.', 401);
        }

        $booking = $this->reviewService->getBookingWithOwnershipCheck($bookingId, $user->id);
        if (! $booking) {
            return $this->jsonError('Booking not found.', 404);
        }

        $rideStatus = $this->reviewService->getRideStatus($booking, $tripType);
        if ($rideStatus === 'pending') {
            return $this->jsonError('This ride has not started yet. You can review once the ride is completed.', 400);
        }
        if ($rideStatus === 'ongoing') {
            return $this->jsonError('This ride is currently ongoing. You can review once the driver marks it as completed.', 400);
        }

        if ($this->reviewService->getExistingReview($bookingId, $tripType)) {
            return $this->jsonError('You have already reviewed this ride.', 400);
        }

        return $this->jsonSuccess('Review form data retrieved successfully', [
            'booking' => $booking,
            'trip_type' => $tripType,
            'form_fields' => [
                'overall_rating' => 'required|integer|min:1|max:5',
                'driver_rating' => 'required|integer|min:1|max:5',
                'vehicle_rating' => 'required|integer|min:1|max:5',
                'punctuality_rating' => 'required|integer|min:1|max:5',
                'safety_rating' => 'required|integer|min:1|max:5',
                'comfort_rating' => 'required|integer|min:1|max:5',
                'review_text' => 'nullable|string|max:1000',
            ],
        ]);
    }

    public function apiSubmitReview(Request $request, $bookingId, $tripType = 'go'): JsonResponse
    {
        $user = $this->getApiUser($request);
        if (! $user) {
            return $this->jsonError('Please login to submit reviews.', 401);
        }

        $booking = $this->reviewService->getBookingWithOwnershipCheck($bookingId, $user->id);
        if (! $booking) {
            return $this->jsonError('Booking not found.', 404);
        }

        $rideStatus = $this->reviewService->getRideStatus($booking, $tripType);
        if ($rideStatus !== 'completed') {
            return $this->jsonError('You can only review completed rides.', 400);
        }

        if ($this->reviewService->getExistingReview($bookingId, $tripType)) {
            return $this->jsonError('You have already reviewed this ride.', 400);
        }

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'overall_rating' => 'required|integer|min:1|max:5',
            'driver_rating' => 'required|integer|min:1|max:5',
            'vehicle_rating' => 'required|integer|min:1|max:5',
            'punctuality_rating' => 'required|integer|min:1|max:5',
            'safety_rating' => 'required|integer|min:1|max:5',
            'comfort_rating' => 'required|integer|min:1|max:5',
            'review_text' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return $this->jsonError('Validation failed', 422, $validator->errors());
        }

        try {
            $review = $this->reviewService->createReview($booking, $user->id, $tripType, [
                'overall_rating' => $request->overall_rating,
                'driver_rating' => $request->driver_rating,
                'vehicle_rating' => $request->vehicle_rating,
                'punctuality_rating' => $request->punctuality_rating,
                'safety_rating' => $request->safety_rating,
                'comfort_rating' => $request->comfort_rating,
            ], $request->review_text);

            return $this->jsonSuccess('Review submitted successfully!', [
                'review' => $review,
                'booking' => $booking,
            ]);
        } catch (\Exception $e) {
            return $this->jsonError('Failed to submit review. Please try again.', 500);
        }
    }

    public function apiViewRideReviews(Request $request, $rideId): JsonResponse
    {
        $user = $this->getApiUser($request);
        if (! $user) {
            return $this->jsonError('Please login to view reviews.', 401);
        }

        $ride = Ride::find($rideId);
        if (! $ride) {
            return $this->jsonError('Ride not found.', 404);
        }

        $reviews = RideReview::with(['user', 'ride'])
            ->where('ride_id', $rideId)
            ->orderBy('created_at', 'desc')
            ->get();

        return $this->jsonSuccess('Ride reviews retrieved successfully', [
            'ride' => $ride,
            'reviews' => $reviews,
        ]);
    }

    public function apiViewAllReviews(Request $request): JsonResponse
    {
        $user = $this->getApiUser($request);
        if (! $user) {
            return $this->jsonError('Please login to view reviews.', 401);
        }

        $reviews = RideReview::with(['user', 'ride'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return $this->jsonPaginated('All reviews retrieved successfully', $reviews);
    }
}
