<?php

namespace App\Http\Controllers;

use App\Models\DriverDocument;
use App\Models\User;
use App\Services\DriverStatsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class DriverProfileController extends Controller
{
    protected $driverStatsService;

    public function __construct()
    {
        $this->driverStatsService = app(DriverStatsService::class);
    }

    public function show(): View|RedirectResponse
    {
        $user = $this->getWebUser();
        if (! $user) {
            return $this->webRedirectLogin();
        }

        $driverDocuments = DriverDocument::where('user_id', $user->id)->first();

        return view('driver-profile', compact('user', 'driverDocuments'));
    }

    public function updateVehiclePhotos(Request $request): RedirectResponse
    {
        $user = $this->getWebUser();
        if (! $user) {
            return $this->webRedirectLogin();
        }

        $request->validate([
            'vehicle_photo_1' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'vehicle_photo_2' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'vehicle_photo_3' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $driverDocuments = DriverDocument::where('user_id', $user->id)->first();
        if (! $driverDocuments) {
            return redirect()->back()->with('error', 'Driver documents not found.');
        }

        $updated = $this->handlePhotoUploads($request, $driverDocuments);

        if ($updated) {
            $driverDocuments->save();

            return redirect()->back()->with('success', 'Vehicle photos updated successfully.');
        }

        return redirect()->back()->with('info', 'No changes were made.');
    }

    private function handlePhotoUploads(Request $request, DriverDocument $docs): bool
    {
        $updated = false;
        foreach (['vehicle_photo_1', 'vehicle_photo_2', 'vehicle_photo_3'] as $field) {
            if ($request->hasFile($field)) {
                if ($docs->$field) {
                    Storage::disk('public')->delete($docs->$field);
                }
                $docs->$field = $request->file($field)->store('driver-documents', 'public');
                $updated = true;
            }
        }

        return $updated;
    }

    public function showPublic($driverId): View
    {
        $user = User::where('id', $driverId)->where('role', 'driver')->firstOrFail();
        $driverDocuments = DriverDocument::where('user_id', $user->id)->first();

        $stats = $this->driverStatsService->getPublicProfileStats($user);

        return view('driver-profile-public', compact(
            'user',
            'driverDocuments',
            'stats'
        ) + [
            'reviews' => $stats['reviews'],
            'totalReviews' => $stats['total_reviews'],
            'averageOverallRating' => $stats['average_overall_rating'],
            'averageDriverRating' => $stats['average_driver_rating'],
            'averageVehicleRating' => $stats['average_vehicle_rating'],
            'ratingDistribution' => $stats['rating_distribution'],
            'previousRides' => $stats['previous_rides'],
            'filteredAvailableRides' => $stats['filtered_available_rides'],
            'filteredReturnRides' => $stats['filtered_return_rides'],
        ]);
    }

    // API Methods
    public function apiShow(Request $request): JsonResponse
    {
        $user = $this->getApiUser($request);
        if (! $user) {
            return $this->jsonError('Please login to access your driver profile.', 401);
        }

        $driverDocuments = DriverDocument::where('user_id', $user->id)->first();

        return $this->jsonSuccess('Driver profile retrieved successfully', [
            'user' => $user,
            'driver_documents' => $driverDocuments,
        ]);
    }

    public function apiUpdateVehiclePhotos(Request $request): JsonResponse
    {
        $user = $this->getApiUser($request);
        if (! $user) {
            return $this->jsonError('Please login to update vehicle photos.', 401);
        }

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'vehicle_photo_1' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'vehicle_photo_2' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'vehicle_photo_3' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return $this->jsonError('Validation failed', 422, $validator->errors());
        }

        $driverDocuments = DriverDocument::where('user_id', $user->id)->first();
        if (! $driverDocuments) {
            return $this->jsonError('Driver documents not found.', 404);
        }

        $updated = $this->handlePhotoUploads($request, $driverDocuments);

        if ($updated) {
            $driverDocuments->save();

            return $this->jsonSuccess('Vehicle photos updated successfully.', [
                'driver_documents' => $driverDocuments,
            ]);
        }

        return $this->jsonError('No changes were made.', 400);
    }

    public function apiShowPublic(Request $request, $driverId): JsonResponse
    {
        $user = User::where('id', $driverId)->where('role', 'driver')->first();
        if (! $user) {
            return $this->jsonError('Driver not found.', 404);
        }

        $driverDocuments = DriverDocument::where('user_id', $user->id)->first();
        $stats = $this->driverStatsService->getPublicProfileStats($user);

        return $this->jsonSuccess('Driver public profile retrieved successfully', [
            'driver' => $user,
            'driver_documents' => $driverDocuments,
            'reviews' => $stats['reviews'],
            'statistics' => [
                'total_reviews' => $stats['total_reviews'],
                'average_overall_rating' => $stats['average_overall_rating'],
                'average_driver_rating' => $stats['average_driver_rating'],
                'average_vehicle_rating' => $stats['average_vehicle_rating'],
                'rating_distribution' => $stats['rating_distribution'],
            ],
            'previous_rides' => $stats['previous_rides'],
            'available_rides' => $stats['filtered_available_rides'],
            'available_return_rides' => $stats['filtered_return_rides'],
        ]);
    }
}
