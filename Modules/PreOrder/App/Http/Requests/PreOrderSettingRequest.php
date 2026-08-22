<?php

namespace Modules\PreOrder\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PreOrderSettingRequest extends FormRequest
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
        return [
            "pre_order_status" => "nullable",
            "pre_order_for_shop" => "nullable",
            "shop_commission" => "nullable|numeric",
            "commission_type" => "nullable|string|in:percentage,fixed",
            "pre_order_policy" => "nullable|string|max:300",
            "pre_order_refund_policy" => "nullable|string|max:300",
        ];
    }
}
