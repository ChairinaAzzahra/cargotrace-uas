<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TransitionStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isAdmin() || $this->user()->isOperator();
    }

    public function rules(): array
    {
        return [
            'status' => 'required|in:CREATED,BOOKED,PICKED_UP,IN_TRANSIT,DELIVERED',
            'description' => 'nullable|string|max:500',
            'location_id' => 'nullable|exists:locations,id',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'event_time' => 'nullable|date',
        ];
    }
}
