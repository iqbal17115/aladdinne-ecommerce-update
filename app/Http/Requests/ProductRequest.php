<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductRequest extends FormRequest
{
    /**
     * Maximum value the `quantity` and `min_order_quantity` columns can hold,
     * they are signed INT columns in the database.
     */
    public const MAX_QUANTITY = 2147483647;

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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $max =$this->price;
        $additionThumbnail = $this->product?->medias?->isNotEmpty() ? 'nullable' : 'nullable';
        $thumbnail = $this->product?->media ? 'nullable' : 'required';

        if ($this->is('api/*')) {
            $additionThumbnail = 'nullable';
        }
        if($this->discount_type == 0){
            $max =100;
        }

        return [
            'name' => 'required|string|max:191',
            'name_ar' => 'nullable|string|max:191',
            'slug' => [
                'nullable',
                'string',
                'max:191',
                Rule::unique('products', 'slug')->ignore($this->product?->id),
            ],
            'description' => 'required|string',
            'description_ar' => 'nullable|string',
            'short_description' => 'required|string|max:191',
            'short_description_ar' => 'nullable|string|max:191',
            'category' => 'required|exists:categories,id',
            'sub_category' => 'nullable|array|exists:sub_categories,id',
            'brand' => 'nullable|exists:brands,id',
            'code' => 'required|numeric|digits_between:5,25',
            'color' => 'nullable|array',
            'color.*.id' => 'nullable|exists:colors,id',
            'color.*.color_img' => 'nullable|string|max:255',
            'color.*.price' => 'nullable|numeric|min:0',
            'size' => 'nullable|array',
            'size.*.id' => 'nullable|exists:sizes,id',
            'size.*.price' => 'nullable|numeric|min:0',
            'unit' => 'nullable|exists:units,id',
            'buy_price' => 'nullable|numeric|min:0',
            'price' => 'required|numeric|min:0',
            'discount_type' => 'nullable|in:0,1',
            'discount' => 'nullable|numeric|min:0|max:'.$max,
            // 'discount_price' => 'nullable|numeric|min:0|max:'.$this->price,
            'quantity' => 'required|integer|min:0|max:'.self::MAX_QUANTITY,
            'min_order_quantity' => 'nullable|integer|min:0|max:'.self::MAX_QUANTITY,
            'meta_title' => 'nullable|string|max:191',
            'meta_description' => 'nullable|string|max:200',
            'thumbnail' => "nullable|string|max:255",
            'additionThumbnail' => "$additionThumbnail|array",
            'additionThumbnail.*' => 'max:255',

            'previousThumbnail' => 'nullable|array',
            'previousThumbnail.*.id' => 'nullable|exists:media,id',
            'previousThumbnail.*.file' => 'nullable|file|mimes:png,jpg,jpeg,webp|max:2048',
            'product_weight' => 'nullable|numeric|min:0',
        ];
    }

    public function messages(): array
    {
        $request = request();
        if ($request->is('api/*')) {
            $header = strtolower($request->header('accept-language'));
            $lan = (preg_match('/^[a-z]+$/', $header)) ? $header : 'en';
            app()->setLocale($lan);
        }

        return [
            'name.required' => __('The name field is required.'),
            'name.max' => __('The name may not be greater than 191 characters.'),
            'slug.unique' => __('The slug has already been taken.'),
            'slug.max' => __('The slug may not be greater than 191 characters.'),
            'description.required' => __('The description field is required.'),
            'short_description.required' => __('The short description field is required.'),
            'short_description.max' => __('The short description may not be greater than 191 characters.'),
            'category.required' => __('The category field is required.'),
            'category.exists' => __('The selected category is invalid.'),
            'code.required' => __('The code field is required.'),
            'code.unique' => __('The code has already been taken.'),
            'code.numeric' => __('The code must be a number.'),
            'code.digits_between' => __('The code must be 5-7 digits.'),
            'price.required' => __('The price field is required.'),
            'price.numeric' => __('The price must be a number.'),
            'price.min' => __('The price must be at least 0.'),
            'discount_price.numeric' => __('The discount price must be a number.'),
            'discount_price.min' => __('The discount price must be at least 0.'),
            'discount_price.max' => __('The discount price must be less than price.'),
            'buy_price.numeric' => __('The buy price must be a number.'),
            'buy_price.min' => __('The buy price must be at least 0.'),
            'discount_type.in' => __('The discount type must be 0 or 1.'),
            'discount.numeric' => __('The discount must be a number.'),
            'discount.min' => __('The discount must be at least 0.'),
            'discount.max' => __('The discount must be less than price.'),
            'quantity.required' => __('The quantity field is required.'),
            'quantity.integer' => __('The quantity must be an integer.'),
            'quantity.min' => __('The quantity must be at least 0.'),
            'quantity.max' => __('The quantity may not be greater than :max.', ['max' => self::MAX_QUANTITY]),
            'min_order_quantity.required' => __('The min order quantity field is required.'),
            'min_order_quantity.integer' => __('The min order quantity must be an integer.'),
            'min_order_quantity.min' => __('The min order quantity must be at least 0.'),
            'min_order_quantity.max' => __('The min order quantity may not be greater than :max.', ['max' => self::MAX_QUANTITY]),
            'thumbnail.required' => __('The thumbnail field is required.'),
            'thumbnail.image' => __('The thumbnail must be an image.'),
            'thumbnail.mimes' => __('The thumbnail must be a file of type: png, jpg, jpeg, webp.'),
            'thumbnail.max' => __('The thumbnail may not be greater than 2048 kilobytes.'),
            'additionThumbnail.required' => __('The addition thumbnail field is required.'),
            'additionThumbnail.image' => __('The addition thumbnail must be an image.'),
            'additionThumbnail.mimes' => __('The addition thumbnail must be a file of type: png, jpg, jpeg, webp.'),
            'additionThumbnail.max' => __('The addition thumbnail may not be greater than 2048 kilobytes.'),
        ];
    }
}
