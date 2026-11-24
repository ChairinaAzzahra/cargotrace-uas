<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTrackingEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isAdmin() || $this->user()->isOperator();
    }

    public function rules(): array
    {
        return [
            'event_type' => 'required|string|max:100',
            'description' => 'required|string|max:1000',
            'location_id' => 'nullable|exists:locations,id',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'event_time' => 'nullable|date',
            'is_exception' => 'nullable|boolean',
        ];
    }
}
