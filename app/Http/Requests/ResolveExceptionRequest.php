<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ResolveExceptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isAdmin() || $this->user()->isOperator();
    }

    public function rules(): array
    {
        return [
            'resolution_notes' => 'required|string|max:1000',
        ];
    }
}
