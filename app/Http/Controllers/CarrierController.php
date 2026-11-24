<?php

namespace App\Http\Controllers;

use App\Models\Carrier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CarrierController extends Controller
{
    public function index(): View
    {
        $carriers = Carrier::withCount('shipments')->orderBy('name')->paginate(15);
        return view('carriers.index', compact('carriers'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|max:10|unique:carriers,code',
            'name' => 'required|string|max:255',
            'contact_email' => 'nullable|email',
            'contact_phone' => 'nullable|string|max:20',
            'tracking_url_template' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');
        Carrier::create($validated);

        return redirect()->route('carriers.index')->with('success', 'Carrier added successfully.');
    }
}
