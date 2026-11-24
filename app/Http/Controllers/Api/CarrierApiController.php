<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Carrier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CarrierApiController extends Controller
{
    public function index(): JsonResponse
    {
        $carriers = Carrier::where('is_active', true)->orderBy('name')->get();
        return response()->json(['success' => true, 'data' => $carriers], 200);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|max:10|unique:carriers,code',
            'name' => 'required|string|max:255',
            'contact_email' => 'nullable|email',
            'contact_phone' => 'nullable|string|max:20',
            'tracking_url_template' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        $carrier = Carrier::create($validated);
        return response()->json(['success' => true, 'data' => $carrier], 201);
    }
}
