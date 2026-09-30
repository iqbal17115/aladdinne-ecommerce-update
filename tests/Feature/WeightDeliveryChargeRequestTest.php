<?php

namespace Tests\Feature;

use App\Http\Requests\WeightDeliveryChargeRequest;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class WeightDeliveryChargeRequestTest extends TestCase
{
    public function test_adjacent_weight_ranges_pass_validation(): void
    {
        $validator = $this->validatorFor([
            ['min_weight' => 0, 'max_weight' => 2, 'delivery_charge' => 50],
            ['min_weight' => 2, 'max_weight' => 5, 'delivery_charge' => 80],
        ]);

        $this->assertTrue($validator->passes());
    }

    public function test_overlapping_weight_ranges_fail_validation(): void
    {
        $validator = $this->validatorFor([
            ['min_weight' => 0, 'max_weight' => 3, 'delivery_charge' => 50],
            ['min_weight' => 2, 'max_weight' => 5, 'delivery_charge' => 80],
        ]);

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('rules.1.max_weight', $validator->errors()->toArray());
    }

    private function validatorFor(array $rules)
    {
        $request = WeightDeliveryChargeRequest::create('/weight-wise-delivery-charge', 'PUT', [
            'area_id' => null,
            'rules' => $rules,
        ]);

        $validator = Validator::make($request->all(), $request->rules());
        $request->withValidator($validator);

        return $validator;
    }
}