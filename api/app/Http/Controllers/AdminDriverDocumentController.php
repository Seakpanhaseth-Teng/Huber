<?php

namespace App\Http\Controllers;

use App\Models\DriverDocument;
use App\Models\User;
use Illuminate\Http\Request;

class AdminDriverDocumentController extends Controller
{
    public function index()
    {
        // Get all drivers who have documents
        $drivers = User::where('role', 'driver')
            ->whereHas('driverDocuments')
            ->with('driverDocuments')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        /** @phpstan-var view-string $view */
        $view = 'admin.driver_documents.index';
        return view($view, compact('drivers'));
    }

    public function show($id)
    {
        $driver = User::where('role', 'driver')->findOrFail($id);
        $driverDocument = DriverDocument::where('user_id', $driver->id)->first();

        /** @phpstan-var view-string $view */
        $view = 'admin.driver_documents.show';
        return view($view, compact('driver', 'driverDocument'));
    }

    public function verify($id)
    {
        $driver = User::where('role', 'driver')->findOrFail($id);
        $driver->update(['is_verified' => true]);

        return redirect()->route('admin.driver_documents.show', $driver->id)
            ->with('success', 'Driver has been verified successfully!');
    }

    public function unverify($id)
    {
        $driver = User::where('role', 'driver')->findOrFail($id);
        $driver->update(['is_verified' => false]);

        return redirect()->route('admin.driver_documents.show', $driver->id)
            ->with('success', 'Driver verification has been revoked.');
    }

    public function create()
    {
        /** @phpstan-var view-string $view */
        $view = 'admin.driver_documents.create';
        return view($view);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'license_number' => 'required',
        ]);
        DriverDocument::create($validated);

        return redirect()->route('admin.driver_documents.index')->with('success', 'Driver document created successfully');
    }

    public function edit($id)
    {
        $document = DriverDocument::findOrFail($id);

        /** @phpstan-var view-string $view */
        $view = 'admin.driver_documents.edit';
        return view($view, compact('document'));
    }

    public function update(Request $request, $id)
    {
        $document = DriverDocument::findOrFail($id);
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'license_number' => 'required',
        ]);
        $document->update($validated);

        return redirect()->route('admin.driver_documents.index')->with('success', 'Driver document updated successfully');
    }

    public function destroy($id)
    {
        $document = DriverDocument::findOrFail($id);
        $document->delete();

        return redirect()->route('admin.driver_documents.index')->with('success', 'Driver document deleted successfully');
    }

    // API: List driver documents
    public function apiIndex()
    {
        $drivers = User::where('role', 'driver')
            ->whereHas('driverDocuments')
            ->with('driverDocuments')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $drivers,
        ]);
    }

    // API: Show driver document
    public function apiShow($id)
    {
        $driver = User::where('role', 'driver')->findOrFail($id);
        $driverDocument = \App\Models\DriverDocument::where('user_id', $driver->id)->first();

        return response()->json([
            'success' => true,
            'data' => [
                'driver' => $driver,
                'driver_document' => $driverDocument,
            ],
        ]);
    }

    // API: Verify driver
    public function apiVerify($id)
    {
        $driver = User::where('role', 'driver')->findOrFail($id);
        $driver->update(['is_verified' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Driver has been verified successfully!',
        ]);
    }

    // API: Unverify driver
    public function apiUnverify($id)
    {
        $driver = User::where('role', 'driver')->findOrFail($id);
        $driver->update(['is_verified' => false]);

        return response()->json([
            'success' => true,
            'message' => 'Driver verification has been revoked.',
        ]);
    }

    // API: Store driver document
    public function apiStore(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'license_number' => 'required',
        ]);
        $document = \App\Models\DriverDocument::create($validated);

        return response()->json([
            'success' => true,
            'data' => $document,
        ]);
    }

    // API: Update driver document
    public function apiUpdate(Request $request, $id)
    {
        $document = \App\Models\DriverDocument::findOrFail($id);
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'license_number' => 'required',
        ]);
        $document->update($validated);

        return response()->json([
            'success' => true,
            'data' => $document,
        ]);
    }

    // API: Delete driver document
    public function apiDestroy($id)
    {
        $document = \App\Models\DriverDocument::findOrFail($id);
        $document->delete();

        return response()->json([
            'success' => true,
            'message' => 'Driver document deleted successfully.',
        ]);
    }
}
