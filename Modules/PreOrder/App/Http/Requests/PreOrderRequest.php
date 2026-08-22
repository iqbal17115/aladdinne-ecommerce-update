<?php

namespace Modules\PreOrder\App\Http\Requests;

use App\Enums\PaymentMethod;
use Illuminate\Validation\Rule;
use App\Models\Scopes\PreOderProduct;
use Illuminate\Validation\Rules\Enum;
use App\Repositories\ProductRepository;
use Illuminate\Foundation\Http\FormRequest;

class PreOrderRequest extends FormRequest
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
        $customerId = auth()->user()->customer->id;
        $product = ProductRepository::query()->withoutGlobalScope(PreOderProduct::class)->where('id', $this->product_id)->first();
        return [
            "product_id" => "required|exists:products,id",
            "quantity" => [
                'required',
                'integer',
                'min:' . ($product?->min_order_quantity ?? 1),
                'max:' . ($product?->preorder_quantity_limit ?? PHP_INT_MAX),
            ],
            "customer_note" => "nullable|string",
            "address_id" => [
                'required',
                Rule::exists('addresses', 'id')
                    ->where('customer_id', $customerId),
            ],
            'payment_method' => [
                'required',
                function ($attribute, $value, $fail) {
                    // Accept "Cash Payment" (enum value) or any gateway name the
                    // controller can resolve to a PaymentMethod case (e.g. "stripe").
                    $isValid = $value === PaymentMethod::CASH->value
                        || in_array(strtoupper((string) $value), array_column(PaymentMethod::cases(), 'name'), true);
                    if (! $isValid) {
                        $methods = implode(', ', array_column(PaymentMethod::cases(), 'value'));
                        $fail("Invalid payment method. Available methods: {$methods}.");
                    }
                },
            ],
            'is_prepay' => 'nullable|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'payment_method.required' => 'Payment method is required.',
        ];
    }
}
