<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreShipmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isAdmin() || $this->user()->isOperator();
    }

    public function rules(): array
    {
        return [
            'shipment_number' => 'nullable|string|max:30|unique:shipments,shipment_number',
            'customer_id' => 'required|exists:users,id',
            'carrier_id' => 'required|exists:carriers,id',
            'origin_id' => 'required|exists:locations,id',
            'destination_id' => 'required|exists:locations,id|different:origin_id',
            'cargo_type' => 'required|string|max:100',
            'total_weight_kg' => 'required|numeric|min:0.01',
            'total_volume_cbm' => 'required|numeric|min:0.01',
            'expected_departure_at' => 'required|date',
            'expected_arrival_at' => 'required|date|after:expected_departure_at',
            'estimated_arrival_at' => 'nullable|date',
            'notes' => 'nullable|string|max:1000',
            'items' => 'nullable|array',
            'items.*.item_description' => 'required_with:items|string|max:255',
            'items.*.hs_code' => 'nullable|string|max:20',
            'items.*.quantity' => 'required_with:items|integer|min:1',
            'items.*.weight_kg' => 'nullable|numeric|min:0',
            'items.*.volume_cbm' => 'nullable|numeric|min:0',
        ];
    }
}
