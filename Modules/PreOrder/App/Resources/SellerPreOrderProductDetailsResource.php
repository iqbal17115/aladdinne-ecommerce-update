<?php

namespace Modules\PreOrder\App\Resources;

use App\Http\Resources\BrandResource;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\SubCategoryResource;
use App\Http\Resources\UnitResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SellerPreOrderProductDetailsResource extends JsonResource
{
    /**
     * Transform the resource into an array (seller pre-order product details).
     */
    public function toArray(Request $request): array
    {
        $isEcommerce = config('app.project_key') === 'ReadyEcommerce';

        $data = [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'code' => $this->code,
            'thumbnail' => $this->thumbnail,
            'additional_thumbnail' => $this->additionalThumbnails(),
            'short_description' => $this->short_description,
            'description' => $this->description,
            'price' => (float) $this->price,
            'discount_price' => (float) $this->discount_price,
            'buy_price' => (float) $this->buy_price,
            'quantity' => (int) $this->quantity,
            'min_order_quantity' => (int) $this->min_order_quantity,
            'brand' => $this->brand ? BrandResource::make($this->brand) : null,
            'categories' => CategoryResource::collection($this->categories),
            'category_ids' => $this->categories->pluck('id'),
            'meta_title' => $this->meta_title,
            'meta_description' => $this->meta_description,
            'meta_keywords' => $this->meta_keywords ? explode(',', $this->meta_keywords) : [],
            // Pre-order specific fields
            'is_active' => (bool) $this->is_active,
            'is_available' => (bool) $this->is_available,
            'is_refund' => (bool) $this->is_refund,
            'is_prepay' => (bool) $this->is_prepay,
            'prepay_amount' => (float) $this->prepay_amount,
            'preorder_quantity_limit' => (int) ($this->preorder_quantity_limit ?? 1),
            'preorder_notice' => $this->preorder_notice ?? '',
            'expected_delivery_date' => $this->expected_delivery_date ?? '',
        ];

        if ($isEcommerce) {
            $data['unit'] = $this->unit_id;
            $data['unit_name'] = $this->unit?->name;
            $data['unit_detail'] = $this->unit ? UnitResource::make($this->unit) : null;
            $data['sub_categories'] = SubCategoryResource::collection($this->subcategories);
            $data['sub_category_ids'] = $this->subcategories->pluck('id');
        } else {
            $data['unit'] = $this->unit;
        }

        return $data;
    }
}
