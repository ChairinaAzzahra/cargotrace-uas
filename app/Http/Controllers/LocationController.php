<?php

namespace App\Http\Controllers;

use App\Models\Location;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LocationController extends Controller
{
    public function index(): View
    {
        $locations = Location::withCount(['originShipments', 'destinationShipments'])->orderBy('name')->paginate(15);
        return view('locations.index', compact('locations'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|max:10|unique:locations,code',
            'name' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'country' => 'required|string|max:100',
            'type' => 'required|in:PORT,AIRPORT,WAREHOUSE,CUSTOMS_CHECKPOINT',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);

        Location::create($validated);

        return redirect()->route('locations.index')->with('success', 'Location port/hub added successfully.');
    }
}
