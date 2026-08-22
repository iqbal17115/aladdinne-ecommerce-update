<?php

namespace Modules\PreOrder\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        // The route parameter is {id}, so $this->product is never bound — resolve
        // the product from the id instead. On update it keeps the existing
        // thumbnail (nullable); on create there is no id, so it stays required.
        $productId = $this->route('id') ?? $this->route('product');
        $product = $productId
            ? \App\Models\Product::withoutGlobalScopes()->find($productId)
            : null;
        $thumbnail = $product?->product_thumbnail ? 'nullable' : 'required';

        $project = config('app.project_key');

        $rules = [
            'name' => 'required|string|max:191',
            'description' => 'required|string',
            'slug' => 'nullable|string|max:191|unique:products,slug,' . ($product?->id ?? $this->id),
            'short_description' => 'required|string|max:191',
            'brand' => 'nullable|exists:brands,id',
            'code' => 'required|numeric|digits_between:5,25',
            'unit' => $project === 'ReadyEcommerce' ? 'required|exists:units,id' : 'required|string|max:200',
            'buy_price' => 'nullable|numeric|min:0',
            'price' => 'required|numeric|min:0',
            'discount_price' => 'nullable|numeric|min:0|lte:price',
            'min_order_quantity' => 'nullable|integer|min:0',
            'meta_title' => 'nullable|string|max:191',
            'meta_description' => 'nullable|string|max:200',
            'thumbnail' => "$thumbnail|max:255",
            'additionThumbnail' => "nullable|array",
            'additionThumbnail.*' => 'max:255',
            'expected_delivery_date' => 'required|string|max:150',
            'prepay_amount' => 'required_if:is_prepay,1|numeric|min:0|lte:price',
            'preorder_quantity_limit' => 'required|integer|min:1',
            'preorder_notice' => 'required|string|max:1500',
        ];

        if ($project === 'ReadyEcommerce') {
            $rules['category'] = 'required|exists:categories,id';
            $rules['sub_category'] = 'nullable|array';
            $rules['sub_category.*'] = 'exists:sub_categories,id';
        } else {
            $rules['categories'] = 'required|array';
            $rules['categories.*'] = 'required|exists:categories,id';
        }

        return $rules;
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
}
