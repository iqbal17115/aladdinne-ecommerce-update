<?php

namespace Modules\PreOrder\App\Repositories;


use App\Repositories\Repository;
use App\Models\Product;
use Illuminate\Support\Str;
use Mews\Purifier\Facades\Purifier;
use App\Repositories\ProductRepository;

class PreOrderProductRepository extends Repository
{
    public static function model()
    {
        return Product::class;
    }
    public static function storeByRequest($request): Product
    {
        $shop = generaleSetting('shop');
        $project = config('app.project_key');
        $unitKey = $project === 'ReadyEcommerce' ? 'unit_id' : 'unit';
        $videoMedia = ProductRepository::videoCreateOrUpdate($request);
        $description = Purifier::clean(ProductRepository::sanitizeUnicode($request->description));
        $keywords = implode(',', $request->meta_keywords ?? []);
        $product = self::create([
            'shop_id' => $shop?->id,
            'name' => $request->name,
            'slug' => $request->slug,
            'description' => $description,
            'short_description' => $request->short_description,
            'brand_id' => $request->brand,
            $unitKey => $request->unit,
            'price' => $request->price,
            'discount_price' => $request->discount_price,
            'quantity' => $request->quantity ?? 0,
            'min_order_quantity' => $request->min_order_quantity ?? 1,
            'product_thumbnail' => $request->thumbnail,
            'code' => $request->code,
            'buy_price' => $request->buy_price ?? 0,
            'is_active' => true,
            'is_new' => true,
            'is_approve' => true,
            'video_id' => $videoMedia ? $videoMedia->id : null,
            'meta_title' => $request->meta_title,
            'meta_description' => $request->meta_description,
            'meta_keywords' => $keywords ? Str::limit($keywords, 200, '') : null,
            'is_preorder' => true,
            'expected_delivery_date' => $request->expected_delivery_date,
            'is_available' => $request->is_available ? true : false,
            'is_refund' => $request->is_refund ? true : false,
            'is_prepay' => $request->is_prepay ? true : false,
            'prepay_amount' => $request->prepay_amount ?? 0,
            'preorder_quantity_limit' => $request->preorder_quantity_limit ?? 1,
            'preorder_notice' => $request->preorder_notice
        ]);

        if ($project === 'ReadyEcommerce') {
            $product->categories()->sync($request->category ? [$request->category] : []);
            $product->subcategories()->sync($request->sub_category ?? []);
        } else {
            $product->categories()->sync($request->categories ?? []);
        }
        $thumbnailColumn = $project === 'ReadyEcommerce' ? 'thumbnail' : 'additional_thumbnail';
        $thumbnails = array_filter($request->additionThumbnail ?? []);
        foreach ($thumbnails as $additionThumbnail) {
            $product->medias()->create([
                $thumbnailColumn => $additionThumbnail
            ]);
        }
        return $product;
    }
    public static function updateByRequest($request, Product $product): Product
    {
        $project = config('app.project_key');
        $unitKey = $project === 'ReadyEcommerce' ? 'unit_id' : 'unit';
        $videoMedia = ProductRepository::videoCreateOrUpdate($request, $product);
        $description = Purifier::clean(ProductRepository::sanitizeUnicode($request->description));
        $keywords = implode(',', $request->meta_keywords ?? []);
        self::update($product, [
            'name' => $request->name,
            'slug' => $request->slug,
            'description' => $description,
            'short_description' => $request->short_description,
            'brand_id' => $request->brand ?? null,
            $unitKey => $request->unit,
            'price' => $request->price,
            'discount_price' => $request->discount_price,
            'quantity' => $request->quantity ?? 0,
            'min_order_quantity' => $request->min_order_quantity ?? 1,
            'product_thumbnail' => $request->thumbnail ??  $product->product_thumbnail,
            'code' => $request->code,
            'buy_price' => $request->buy_price ?? 0,
            'is_active' => $product->is_active,
            'is_new' => false,
            'unit_measurement_add' => $request->unit_measurement_add ?? 0,
            'is_approve' => $product->is_approve,
            'video_id' => $videoMedia ? $videoMedia->id : null,
            'meta_title' => $request->meta_title,
            'meta_description' => $request->meta_description,
            'meta_keywords' => $keywords ? Str::limit($keywords, 200, '') : null,
            'is_preorder' => true,
            'expected_delivery_date' => $request->expected_delivery_date,
            'is_available' => $request->is_available ? true : false,
            'is_refund' => $request->is_refund ? true : false,
            'is_prepay' => $request->is_prepay ? true : false,
            'prepay_amount' => $request->prepay_amount ?? 0,
            'preorder_quantity_limit' => $request->preorder_quantity_limit ?? 1,
            'preorder_notice' => $request->preorder_notice
        ]);
        if ($project === 'ReadyEcommerce') {
            $product->categories()->sync($request->category ? [$request->category] : []);
            $product->subcategories()->sync($request->sub_category ?? []);
        } else {
            $product->categories()->sync($request->categories ?? []);
        }
        // additional thumbnail
        $thumbnailColumn = $project === 'ReadyEcommerce' ? 'thumbnail' : 'additional_thumbnail';
        $thumbnails = array_filter($request->additionThumbnail ?? []);
        $product->medias()->whereNotIn($thumbnailColumn, $thumbnails)->delete();
        foreach ($thumbnails as $thumbnail) {
            $product->medias()->updateOrCreate(
                [
                    'product_id' => $product->id,
                    $thumbnailColumn => $thumbnail
                ],
                []
            );
        }
        return $product;
    }
}
