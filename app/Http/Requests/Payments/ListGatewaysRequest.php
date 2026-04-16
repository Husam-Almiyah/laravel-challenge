<?php

namespace App\Http\Requests\Payments;

use App\Domains\Catalog\Enums\ModuleEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ListGatewaysRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'city_id' => 'required|exists:cities,id',
            'amount' => 'required|numeric|min:0',
            'modules' => 'nullable|array',
            'modules.*' => [Rule::enum(ModuleEnum::class)],
        ];
    }
}
