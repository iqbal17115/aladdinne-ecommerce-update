<?php

namespace Modules\PreOrder\App\Http\Requests;

use Illuminate\Validation\Rule;
use App\Models\Scopes\PreOderProduct;
use App\Repositories\ProductRepository;
use Illuminate\Foundation\Http\FormRequest;

class CheckoutRequest extends FormRequest
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
            "address_id" => [
                'required',
                Rule::exists('addresses', 'id')
                    ->where('customer_id', $customerId),
            ],
            "quantity" => [
                'required',
                'integer',
                'min:' . ($product?->min_order_quantity ?? 1),
                'max:' . ($product?->preorder_quantity_limit ?? PHP_INT_MAX),
            ],
        ];
    }
}
