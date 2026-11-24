<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateShipmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isAdmin() || $this->user()->isOperator();
    }

    public function rules(): array
    {
        return [
            'carrier_id' => 'sometimes|required|exists:carriers,id',
            'cargo_type' => 'sometimes|required|string|max:100',
            'total_weight_kg' => 'sometimes|required|numeric|min:0.01',
            'total_volume_cbm' => 'sometimes|required|numeric|min:0.01',
            'expected_departure_at' => 'sometimes|required|date',
            'expected_arrival_at' => 'sometimes|required|date',
            'estimated_arrival_at' => 'nullable|date',
            'notes' => 'nullable|string|max:1000',
        ];
    }
}
