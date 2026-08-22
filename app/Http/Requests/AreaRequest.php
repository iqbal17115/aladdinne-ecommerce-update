<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class AreaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     */
    public function rules(): array
    {
        $area = $this->route('area');
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('areas', 'name')->ignore($area?->id),
            ],
            'delivery_amount' => 'required|numeric|min:0',
            'distance' => 'nullable|numeric|min:0',
            'latitude'           => 'nullable|numeric|between:-90,90',
            'longitude'          => 'nullable|numeric|between:-180,180',
            'polygon_coordinates' => 'nullable|json',
            'price_per_km' => 'nullable|numeric|min:0',
            'price_per_min' => 'nullable|numeric|min:0',
        ];
    }
}
