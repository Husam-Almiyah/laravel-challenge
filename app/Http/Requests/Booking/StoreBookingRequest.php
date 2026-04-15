<?php

namespace App\Http\Requests\Booking;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'scheduled_at' => 'required|date|after:now',
            'address_id' => [
                'required',
                Rule::exists('addresses', 'id')->where('user_id', $this->user()->id),
            ],
            'notes' => 'nullable|string|max:500',
        ];
    }
}
