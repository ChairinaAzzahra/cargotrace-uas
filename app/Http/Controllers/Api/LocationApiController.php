<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Location;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LocationApiController extends Controller
{
    public function index(): JsonResponse
    {
        $locations = Location::orderBy('name')->get();
        return response()->json(['success' => true, 'data' => $locations], 200);
    }

    public function store(Request $request): JsonResponse
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

        $location = Location::create($validated);
        return response()->json(['success' => true, 'data' => $location], 201);
    }
}
