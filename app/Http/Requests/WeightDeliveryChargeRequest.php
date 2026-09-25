<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class WeightDeliveryChargeRequest extends FormRequest
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
        $acceptId = $this->deliveryCharge?->id ?? null;
        $areaId = $this->area_id ?? null;

        return [
            'area_id' => ['nullable', 'exists:areas,id'],
            'delivery_charge' => ['required', 'numeric', 'min:20'],
            'min_weight' => [
                'required',
                'numeric',
                'min:0',
                Rule::unique('weight_delivery_charges', 'min_weight')
                    ->where(fn ($query) => $query->where('area_id', $areaId))
                    ->ignore($acceptId),
            ],
            'max_weight' => [
                'required',
                'numeric',
                'min:'.$this->min_weight,
                Rule::unique('weight_delivery_charges', 'max_weight')
                    ->where(fn ($query) => $query->where('area_id', $areaId))
                    ->ignore($acceptId),
            ],
        ];
    }
}
