<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

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

        return [
            'delivery_charge' => ['required', 'numeric', 'min:20'],
            'min_weight' => ['required', 'numeric', 'min:0', 'unique:weight_delivery_charges,min_weight,'.$acceptId],
            'max_weight' => ['required', 'numeric', 'min:'.$this->min_weight,'unique:weight_delivery_charges,max_weight,'.$acceptId],
        ];
    }
}
