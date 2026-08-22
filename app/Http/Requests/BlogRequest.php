<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BlogRequest extends FormRequest
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

        return [
            'title' => 'required|string|max:200',
            'title_ar' => 'nullable|string|max:200',
            'category' => 'required|exists:categories,id',
            'thumbnail' => "$required|string|max:255",
            'description' => 'required|string',
            'description_ar' => 'nullable|string',
            'tags' => 'nullable|array',
        ];
    }
}
