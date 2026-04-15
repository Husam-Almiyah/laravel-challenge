<?php

namespace App\Http\Requests\Cart;

use App\Domains\Catalog\Models\Package;
use App\Domains\Catalog\Models\Service;
use Illuminate\Foundation\Http\FormRequest;

class AddToCartRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'itemable_id' => 'required|ulid',
            'itemable_type' => 'required|in:'.Service::class.','.Package::class,
            'quantity' => 'required|integer|min:1|max:10',
        ];
    }
}
