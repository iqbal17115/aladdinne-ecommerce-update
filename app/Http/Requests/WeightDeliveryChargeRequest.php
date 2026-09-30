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
        $bulkRules = $this->input('rules', []);

        if (is_array($bulkRules) && ! empty($bulkRules)) {
            $rows = [];

            foreach ($bulkRules as $index => $rule) {
                $rows["rules.$index.min_weight"] = ['required', 'numeric', 'min:0'];
                $rows["rules.$index.max_weight"] = ['required', 'numeric', 'min:' . ($rule['min_weight'] ?? 0)];
                $rows["rules.$index.delivery_charge"] = ['required', 'numeric', 'min:0'];
            }

            return [
                'area_id' => ['nullable', 'exists:areas,id'],
                'rules' => ['required', 'array'],
                ...$rows,
            ];
        }

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

    public function withValidator($validator)
    {
        $rules = $this->input('rules', []);

        if (! is_array($rules) || empty($rules)) {
            return;
        }

        $validator->after(function ($validator) use ($rules) {
            $ranges = [];

            foreach ($rules as $index => $rule) {
                $minWeight = (float) ($rule['min_weight'] ?? 0);
                $maxWeight = (float) ($rule['max_weight'] ?? 0);

                if ($maxWeight < $minWeight) {
                    $validator->errors()->add("rules.$index.max_weight", __('Max weight must be greater than or equal to min weight.'));
                    continue;
                }

                foreach ($ranges as $existingIndex => $existing) {
                    if (! ($maxWeight <= $existing['min_weight'] || $minWeight >= $existing['max_weight'])) {
                        $validator->errors()->add("rules.$index.max_weight", __('Weight ranges cannot overlap with the previous range.'));
                        break;
                    }
                }

                $ranges[] = [
                    'min_weight' => $minWeight,
                    'max_weight' => $maxWeight,
                    'index' => $index,
                ];
            }
        });
    }
}
