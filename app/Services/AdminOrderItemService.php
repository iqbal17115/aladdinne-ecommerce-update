<?php

namespace App\Services;

use App\Enums\DiscountType;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AdminOrderItemService
{
    public function update(Order $order, array $items, float $requestedDeliveryCharge, array $newItem = []): void
    {
        DB::transaction(function () use ($order, $items, $requestedDeliveryCharge, $newItem) {
            $order = Order::query()->whereKey($order->id)->lockForUpdate()->firstOrFail();

            if ($order->order_status !== OrderStatus::PENDING || $order->payment_status !== PaymentStatus::PENDING) {

                throw ValidationException::withMessages([
                    'items' => __('Only unpaid pending orders can be edited.'),
                ]);
            }

            $lines = DB::table('order_products')->where('order_id', $order->id)->lockForUpdate()->get();
            $lineIds = $lines->pluck('id')->map(fn ($id) => (int) $id)->all();
            $submittedIds = array_map('intval', array_keys($items));
            sort($lineIds);
            sort($submittedIds);

            if ($lineIds !== $submittedIds || $lines->isEmpty()) {
                throw ValidationException::withMessages([
                    'items' => __('The order items changed. Refresh the page and try again.'),
                ]);
            }

            $payments = $order->payments()->lockForUpdate()->get();
            if ($payments->contains(fn ($payment) => $payment->is_paid)) {
                throw ValidationException::withMessages([
                    'items' => __('This order has a paid payment and cannot be edited.'),
                ]);
            }

            $productIds = $lines->pluck('product_id')->unique();
            $products = Product::withoutGlobalScopes()
                ->with(['sizes', 'colors'])
                ->whereIn('id', $productIds)
                ->lockForUpdate()
                ->get();
            if ($products->count() !== $productIds->count() || $products->contains(fn ($product) => $product->is_digital)) {
                throw ValidationException::withMessages([
                    'items' => __('Orders containing digital products cannot be edited here.'),
                ]);
            }

            /** @var array<int, Product> $productsById */
            $productsById = [];
            foreach ($products as $product) {
                $productsById[(int) $product->id] = $product;
            }

            $changes = [];
            foreach ($lines as $line) {
                $lineInput = $items[$line->id];
                $remove = filter_var($lineInput['remove'] ?? false, FILTER_VALIDATE_BOOLEAN);
                $quantity = $remove ? 0 : (int) $lineInput['quantity'];
                $product = $productsById[$line->product_id];

                $sizeName = $line->size;
                if (array_key_exists('size_id', $lineInput)) {
                    $sizeId = $lineInput['size_id'];
                    $size = $sizeId !== null && $sizeId !== ''
                        ? $product->sizes->firstWhere('id', (int) $sizeId)
                        : null;
                    if ($sizeId !== null && $sizeId !== '' && ! $size) {
                        throw ValidationException::withMessages([
                            'items.'.$line->id.'.size_id' => __('Choose a valid size for :product.', ['product' => $product->name]),
                        ]);
                    }
                    $sizeName = $size?->name;
                }

                $colorName = $line->color;
                if (array_key_exists('color_id', $lineInput)) {
                    $colorId = $lineInput['color_id'];
                    $color = $colorId !== null && $colorId !== ''
                        ? $product->colors->firstWhere('id', (int) $colorId)
                        : null;
                    if ($colorId !== null && $colorId !== '' && ! $color) {
                        throw ValidationException::withMessages([
                            'items.'.$line->id.'.color_id' => __('Choose a valid color for :product.', ['product' => $product->name]),
                        ]);
                    }
                    $colorName = $color?->name;
                }

                $changes[$line->id] = [
                    'quantity' => $quantity,
                    'size' => $sizeName,
                    'color' => $colorName,
                    'price' => round((float) $lineInput['price'], 2),
                    'flash_sale_id' => $this->resolveFlashSaleId($product, $line),
                ];
            }

            $finalLines = [];
            $finalQuantityByProduct = [];

            foreach ($lines as $line) {
                $product = $productsById[$line->product_id];
                $oldQuantity = (int) $line->quantity;
                $change = $changes[$line->id];
                $newQuantity = $change['quantity'];
                $delta = $newQuantity - $oldQuantity;

                if ($delta !== 0 && $line->price === null && $product->flashSales()->exists()) {
                    throw ValidationException::withMessages([
                        'items' => __('The flash-sale status for :product cannot be determined safely.', ['product' => $product->name]),
                    ]);
                }

                if ($delta > 0 && (int) $product->quantity < $delta) {
                    throw ValidationException::withMessages([
                        'items' => __('Not enough stock is available for :product.', ['product' => $product->name]),
                    ]);
                }

                if ($delta !== 0) {
                    $product->quantity = (int) $product->quantity - $delta;
                    $product->save();
                    $this->adjustFlashSaleQuantity($product, $change['flash_sale_id'], $delta);
                }

                if ($newQuantity === 0) {
                    DB::table('order_products')->where('id', $line->id)->delete();
                } elseif (
                    $delta !== 0
                    || $change['size'] !== $line->size
                    || $change['color'] !== $line->color
                    || $change['price'] !== round((float) $line->price, 2)
                    || (int) ($change['flash_sale_id'] ?? 0) !== (int) ($line->flash_sale_id ?? 0)
                ) {
                    DB::table('order_products')->where('id', $line->id)->update([
                        'quantity' => $newQuantity,
                        'size' => $change['size'],
                        'color' => $change['color'],
                        'price' => $change['price'],
                        'flash_sale_id' => $change['flash_sale_id'],
                        'updated_at' => now(),
                    ]);
                }

                if ($newQuantity > 0) {
                    $finalLines[] = [
                        'product' => $product,
                        'price' => $change['price'],
                        'quantity' => $newQuantity,
                    ];
                    $finalQuantityByProduct[$line->product_id] = ($finalQuantityByProduct[$line->product_id] ?? 0) + $newQuantity;
                }
            }

            if (! empty($newItem['product_id'])) {
                $newLine = $this->attachNewProduct($order, $newItem);
                $finalLines[] = $newLine;
                $productId = $newLine['product']->id;
                $finalQuantityByProduct[$productId] = ($finalQuantityByProduct[$productId] ?? 0) + $newLine['quantity'];
                $productIds->push($productId);
                $productIds = $productIds->unique();
            }

            if (count($finalLines) < 1) {
                throw ValidationException::withMessages([
                    'items' => __('An order must contain at least one product.'),
                ]);
            }

            $subtotal = 0;
            foreach ($finalLines as $line) {
                $subtotal += $line['price'] * $line['quantity'];
            }
            $subtotal = round($subtotal, 2);
            $deliveryCharge = round($requestedDeliveryCharge, 2);

            $taxAmount = 0;
            foreach ($order->vatTaxes()->lockForUpdate()->get() as $tax) {
                $tax->amount = round($subtotal * ((float) $tax->percentage / 100), 2);
                $tax->save();
                $taxAmount += (float) $tax->amount;
            }

            $couponDiscount = $this->calculateCouponDiscount($order, $subtotal);
            $payableAmount = round($subtotal + $deliveryCharge + $taxAmount - $couponDiscount, 2);
            $amountDelta = round($payableAmount - (float) $order->payable_amount, 2);

            foreach ($payments as $payment) {
                $payment->amount = round((float) $payment->amount + $amountDelta, 2);
                if ($payment->amount < 0) {
                    throw ValidationException::withMessages([
                        'items' => __('The payment amount could not be safely recalculated.'),
                    ]);
                }
                $payment->save();
            }

            $order->update([
                'total_amount' => $subtotal,
                'delivery_charge' => $deliveryCharge,
                'tax_amount' => $taxAmount,
                'coupon_discount' => $couponDiscount,
                'payable_amount' => $payableAmount,
            ]);
        });
    }

    private function attachNewProduct(Order $order, array $newItem): array
    {
        $quantity = (int) ($newItem['quantity'] ?? 0);
        $product = Product::query()
            ->where('shop_id', $order->shop_id)
            ->isActive()
            ->where('is_digital', false)
            ->whereKey((int) $newItem['product_id'])
            ->lockForUpdate()
            ->first();

        if (! $product) {
            throw ValidationException::withMessages([
                'new_item.product_id' => __('Choose an active physical product from this order’s shop.'),
            ]);
        }

        if ($quantity < 1 || (int) $product->quantity < $quantity) {
            throw ValidationException::withMessages([
                'new_item.quantity' => __('The requested quantity is not available for :product.', ['product' => $product->name]),
            ]);
        }

        $sizes = $product->sizes()->get();
        $size = $newItem['size_id'] ?? null ? $sizes->firstWhere('id', (int) $newItem['size_id']) : null;
        if (($sizes->isNotEmpty() && ! $size) || (! empty($newItem['size_id']) && ! $size)) {
            throw ValidationException::withMessages([
                'new_item.size_id' => __('Choose a valid size for :product.', ['product' => $product->name]),
            ]);
        }

        $colors = $product->colors()->get();
        $color = $newItem['color_id'] ?? null ? $colors->firstWhere('id', (int) $newItem['color_id']) : null;
        if (($colors->isNotEmpty() && ! $color) || (! empty($newItem['color_id']) && ! $color)) {
            throw ValidationException::withMessages([
                'new_item.color_id' => __('Choose a valid color for :product.', ['product' => $product->name]),
            ]);
        }

        $product->quantity = (int) $product->quantity - $quantity;
        $product->save();

        $flashSale = $this->reserveFlashSaleQuantity($product, $quantity);
        $unitPrice = $flashSale['price'] ?? (float) ($product->discount_price > 0 ? $product->discount_price : $product->price);
        $unitPrice += (float) ($size?->pivot?->price ?? 0) + (float) ($color?->pivot?->price ?? 0);
        $unitPrice = round($unitPrice, 2);

        $order->products()->attach($product->id, [
            'quantity' => $quantity,
            'color' => $color?->name,
            'size' => $size?->name,
            'unit' => $product->unit?->name,
            'price' => $unitPrice,
            'flash_sale_id' => $flashSale['id'] ?? null,
            'buying_price' => $product->buyingPrice() ?? 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return [
            'product' => $product,
            'price' => $unitPrice,
            'flash_sale_id' => $flashSale['id'] ?? null,
            'quantity' => $quantity,
        ];
    }

    private function reserveFlashSaleQuantity(Product $product, int $quantity): ?array
    {
        $flashSale = $product->flashSales()->first();
        if (! $flashSale) {
            return null;
        }

        $flashSaleProduct = DB::table('flash_sale_products')
            ->where('flash_sale_id', $flashSale->id)
            ->where('product_id', $product->id)
            ->lockForUpdate()
            ->first();

        if (! $flashSaleProduct || (int) $flashSaleProduct->quantity - (int) $flashSaleProduct->sale_quantity < $quantity) {
            return null;
        }

        DB::table('flash_sale_products')
            ->where('flash_sale_id', $flashSale->id)
            ->where('product_id', $product->id)
            ->update(['sale_quantity' => (int) $flashSaleProduct->sale_quantity + $quantity]);

        return [
            'id' => (int) $flashSale->id,
            'price' => (float) $flashSaleProduct->price,
        ];
    }

    private function resolveFlashSaleId(Product $product, object $line): ?int
    {
        if (! empty($line->flash_sale_id)) {
            return (int) $line->flash_sale_id;
        }

        $flashSale = $product->flashSales()->first();
        if (! $flashSale || $line->price === null) {
            return null;
        }

        $sizePrice = $product->sizes()
            ->where('sizes.name', $line->size)
            ->first()?->pivot?->price ?? 0;
        $colorPrice = $product->colors()
            ->where('colors.name', $line->color)
            ->first()?->pivot?->price ?? 0;
        $flashSaleLinePrice = (float) $flashSale->pivot->price + (float) $sizePrice + (float) $colorPrice;

        if (round((float) $line->price, 2) !== round($flashSaleLinePrice, 2)) {
            $regularPrice = (float) ($product->discount_price > 0 ? $product->discount_price : $product->price)
                + (float) $sizePrice
                + (float) $colorPrice;

            if (round((float) $line->price, 2) === round($regularPrice, 2)) {
                return null;
            }

            throw ValidationException::withMessages([
                'items' => __('The flash-sale price for :product has changed; this order cannot be safely edited.', ['product' => $product->name]),
            ]);
        }

        return (int) $flashSale->id;
    }

    private function adjustFlashSaleQuantity(Product $product, ?int $flashSaleId, int $delta): void
    {
        if (! $flashSaleId) {
            return;
        }

        $saleProduct = DB::table('flash_sale_products')
            ->where('flash_sale_id', $flashSaleId)
            ->where('product_id', $product->id)
            ->lockForUpdate()
            ->first();

        if (! $saleProduct) {
            return;
        }

        $newSaleQuantity = (int) $saleProduct->sale_quantity + $delta;
        if ($newSaleQuantity < 0) {
            throw ValidationException::withMessages([
                'items' => __('Flash-sale quantity could not be safely updated for :product.', ['product' => $product->name]),
            ]);
        }
        if ($delta > 0 && $newSaleQuantity > (int) $saleProduct->quantity) {
            throw ValidationException::withMessages([
                'items' => __('Not enough flash-sale quantity is available for :product.', ['product' => $product->name]),
            ]);
        }

        DB::table('flash_sale_products')
            ->where('flash_sale_id', $flashSaleId)
            ->where('product_id', $product->id)
            ->update(['sale_quantity' => $newSaleQuantity]);
    }

    private function syncPurchaseStockOuts(Order $order, $productIds, array $finalQuantityByProduct): void
    {
        if (! function_exists('module_exists') || ! module_exists('Purchase')) {
            return;
        }

        foreach ($productIds as $productId) {
            $stockOuts = DB::table('product_stock_outs')
                ->where('order_id', $order->id)
                ->where('product_id', $productId)
                ->lockForUpdate()
                ->get();
            $quantity = $finalQuantityByProduct[$productId] ?? 0;

            if ($quantity === 0) {
                DB::table('product_stock_outs')
                    ->where('order_id', $order->id)
                    ->where('product_id', $productId)
                    ->delete();
                continue;
            }

            $stockOut = $stockOuts->first();
            if ($stockOut) {
                DB::table('product_stock_outs')->where('id', $stockOut->id)->update([
                    'quantity' => $quantity,
                    'updated_at' => now(),
                ]);
                DB::table('product_stock_outs')
                    ->where('order_id', $order->id)
                    ->where('product_id', $productId)
                    ->where('id', '!=', $stockOut->id)
                    ->delete();
            } else {
                DB::table('product_stock_outs')->insert([
                    'order_id' => $order->id,
                    'product_id' => $productId,
                    'quantity' => $quantity,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    private function calculateCouponDiscount(Order $order, float $subtotal): float
    {
        $coupon = $order->coupon;
        if (! $coupon || $subtotal < (float) $coupon->min_amount) {
            return 0;
        }

        $discount = $coupon->type === DiscountType::PERCENTAGE
            ? $subtotal * ((float) $coupon->discount / 100)
            : (float) $coupon->discount;

        if ((float) $coupon->max_discount_amount > 0) {
            $discount = min($discount, (float) $coupon->max_discount_amount);
        }

        return round(min($discount, $subtotal), 2);
    }
}