<?php

namespace Tests\Feature;

use App\Http\Requests\AddressRequest;
use Tests\TestCase;

class CheckoutEmailValidationTest extends TestCase
{
    public function test_guest_checkout_email_is_optional()
    {
        $request = AddressRequest::create('/checkout', 'POST', [
            'name' => 'John Doe',
            'phone' => '1234567890',
            'address_line' => '123 Main Street, Box 1',
            'address_type' => 'home',
            'area_id' => 1,
            'email' => null,
        ]);

        $rules = $request->rules();

        $this->assertArrayHasKey('email', $rules);
        $this->assertContains('nullable', $rules['email']);
        $this->assertNotContains('required', $rules['email']);
    }
}
