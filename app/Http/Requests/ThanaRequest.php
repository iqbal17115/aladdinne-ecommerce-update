<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class ThanaRequest extends FormRequest
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
     */
    public function rules(): array
    {
        $thana = $this->route('thana');

        return [
            'area_id' => 'required|exists:areas,id',
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('thanas', 'name')->where('area_id', $this->area_id)->ignore($thana?->id),
            ],
            'shipping_charge' => 'required|numeric|min:0',
        ];
    }
}
