<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RideValidationService
{
    public function rideRules(): array
    {
        return [
            'station_location' => 'required|string|max:255',
            'destination' => 'required|string|max:255',
            'date' => 'required|date|after_or_equal:today',
            'time' => 'required',
            'available_seats' => 'required|integer',
            'is_exclusive' => 'required|boolean',
            'is_two_way' => 'required|boolean',
            'return_station_location' => 'nullable|string|max:255',
            'return_destination' => 'nullable|string|max:255',
            'return_date' => 'nullable|date|after_or_equal:date',
            'return_time' => 'nullable',
            'return_available_seats' => 'nullable|integer',
            'return_is_exclusive' => 'nullable|boolean',
            'station_location_map_url' => 'nullable|url|max:255|regex:/^https:\/\/(www\.)?(google\.com\/maps|maps\.google\.com|maps\.app\.goo\.gl)/i',
            'destination_map_url' => 'nullable|url|max:255|regex:/^https:\/\/(www\.)?(google\.com\/maps|maps\.google\.com|maps\.app\.goo\.gl)/i',
            'return_station_location_map_url' => 'nullable|url|max:255|regex:/^https:\/\/(www\.)?(google\.com\/maps|maps\.google\.com|maps\.app\.goo\.gl)/i',
            'return_destination_map_url' => 'nullable|url|max:255|regex:/^https:\/\/(www\.)?(google\.com\/maps|maps\.google\.com|maps\.app\.goo\.gl)/i',
            'go_to_price_per_person' => 'nullable|numeric|min:0',
            'go_to_exclusive_price' => 'nullable|numeric|min:0',
            'return_price_per_person' => 'nullable|numeric|min:0',
            'return_exclusive_price' => 'nullable|numeric|min:0',
        ];
    }

    public function rideUpdateRules(): array
    {
        return [
            'station_location' => 'required|string|max:255',
            'destination' => 'required|string|max:255',
            'date' => 'required|date|after_or_equal:today',
            'time' => 'required',
            'available_seats' => 'required|integer|min:1',
            'is_exclusive' => 'required|boolean',
            'is_two_way' => 'required|boolean',
            'station_location_map_url' => 'nullable|url|max:255|regex:/^https:\/\/(www\.)?(google\.com\/maps|maps\.google\.com|maps\.app\.goo\.gl)/i',
            'destination_map_url' => 'nullable|url|max:255|regex:/^https:\/\/(www\.)?(google\.com\/maps|maps\.google\.com|maps\.app\.goo\.gl)/i',
            'go_to_price_per_person' => 'nullable|numeric|min:0',
            'go_to_exclusive_price' => 'nullable|numeric|min:0',
        ];
    }

    public function validateRidePricing(Request $request, array &$validated): void
    {
        if ($request->is_exclusive) {
            $request->validate([
                'go_to_exclusive_price' => 'required|numeric|min:0',
            ], [
                'go_to_exclusive_price.required' => 'Exclusive price is required for exclusive rides.',
            ]);
            $validated['go_to_price_per_person'] = null;
        } else {
            $request->validate([
                'go_to_price_per_person' => 'required|numeric|min:0',
            ], [
                'go_to_price_per_person.required' => 'Price per person is required for shared rides.',
            ]);
            $validated['go_to_exclusive_price'] = null;
        }
    }

    public function validateReturnPricing(Request $request, array &$validated): void
    {
        if ($request->is_two_way) {
            if ($request->return_is_exclusive) {
                $request->validate([
                    'return_exclusive_price' => 'required|numeric|min:0',
                ], [
                    'return_exclusive_price.required' => 'Return exclusive price is required for exclusive return rides.',
                ]);
                $validated['return_price_per_person'] = null;
            } else {
                $request->validate([
                    'return_price_per_person' => 'required|numeric|min:0',
                ], [
                    'return_price_per_person.required' => 'Return price per person is required for shared return rides.',
                ]);
                $validated['return_exclusive_price'] = null;
            }
        } else {
            $validated['return_price_per_person'] = null;
            $validated['return_exclusive_price'] = null;
        }
    }

    public function validateApiRidePricing(Request $request, array &$validated): ?array
    {
        if ($request->is_exclusive) {
            $exclusiveValidator = Validator::make($request->all(), [
                'go_to_exclusive_price' => 'required|numeric|min:0',
            ], [
                'go_to_exclusive_price.required' => 'Exclusive price is required for exclusive rides.',
            ]);
            if ($exclusiveValidator->fails()) {
                return $exclusiveValidator->errors()->toArray();
            }
            $validated['go_to_price_per_person'] = null;
        } else {
            $sharedValidator = Validator::make($request->all(), [
                'go_to_price_per_person' => 'required|numeric|min:0',
            ], [
                'go_to_price_per_person.required' => 'Price per person is required for shared rides.',
            ]);
            if ($sharedValidator->fails()) {
                return $sharedValidator->errors()->toArray();
            }
            $validated['go_to_exclusive_price'] = null;
        }

        return null;
    }

    public function validateApiReturnPricing(Request $request, array &$validated): ?array
    {
        if ($request->is_two_way) {
            if ($request->return_is_exclusive) {
                $validator = Validator::make($request->all(), [
                    'return_exclusive_price' => 'required|numeric|min:0',
                ], [
                    'return_exclusive_price.required' => 'Return exclusive price is required for exclusive return rides.',
                ]);
                if ($validator->fails()) {
                    return $validator->errors()->toArray();
                }
                $validated['return_price_per_person'] = null;
            } else {
                $validator = Validator::make($request->all(), [
                    'return_price_per_person' => 'required|numeric|min:0',
                ], [
                    'return_price_per_person.required' => 'Return price per person is required for shared return rides.',
                ]);
                if ($validator->fails()) {
                    return $validator->errors()->toArray();
                }
                $validated['return_exclusive_price'] = null;
            }
        } else {
            $validated['return_price_per_person'] = null;
            $validated['return_exclusive_price'] = null;
        }

        return null;
    }
}
