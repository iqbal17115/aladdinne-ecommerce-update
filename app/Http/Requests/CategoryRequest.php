<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CategoryRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $required = $this->isMethod('put') ? 'nullable' : 'required';
        $iconRequired = $this->isMethod('put') ? 'nullable' : 'required';
        $bannerRequired = $this->isMethod('put') ? 'nullable' : 'nullable';
        $imageExtensions = 'regex:/\.(jpg|jpeg|png|gif|webp|svg)$/i';

        return [
            'name' => ['required', 'string', 'max:255'],
            'name_ar' => ['nullable', 'string', 'max:255'],
            'description_ar' => ['nullable', 'string'],
            'order_by' => ['nullable', 'integer', 'min:0'],
            'thumbnail' => ['nullable', 'string', 'max:255'],
            'icon' => [$iconRequired, 'string', 'max:255'],
            'banner' => [$bannerRequired, 'string', 'max:255', $imageExtensions],
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
            'name.string' => __('The name must be a string.'),
            'name.max' => __('The name may not be greater than 255 characters.'),
            'order_by.integer' => __('The order by field must be a number.'),
            'order_by.min' => __('The order by field must be at least 0.'),
            'thumbnail.required' => __('The thumbnail field is required.'),
            'thumbnail.string' => __('The thumbnail must be a string.'),
            'thumbnail.max' => __('The thumbnail may not be greater than 255 characters.'),
            'icon.required' => __('The icon field is required.'),
            'icon.string' => __('The icon must be a string.'),
            'icon.max' => __('The icon may not be greater than 255 characters.'),
            'banner.required' => __('The banner field is required.'),
            'banner.string' => __('The banner must be a string.'),
            'banner.max' => __('The banner may not be greater than 255 characters.'),
            'banner.regex' => __('The banner must be an image file.'),
        ];
    }
}
