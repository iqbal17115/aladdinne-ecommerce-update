<?php

namespace Modules\PreOrder\App\Repositories;


use App\Repositories\Repository;
use App\Models\Scopes\PreOderProduct;
use Illuminate\Support\Facades\DB;
use Modules\PreOrder\App\Models\PreOrderSetting;
use Modules\PreOrder\App\Repositories\PreOrderProductRepository;

class PreOrderSettingRepository extends Repository
{
    public static function model()
    {
        return PreOrderSetting::class;
    }
    public static function storeByRequestFrom($request)
    {
        $preOrderSetting = self::first();
        $rootShopId = generaleSetting('rootShop')->id;

        return DB::transaction(function () use ($request, $preOrderSetting, $rootShopId) {
            $preOrderSetting = self::query()->updateOrCreate(
                [
                    'id' => $preOrderSetting?->id,
                ],
                [
                    'pre_order_active' => $request->pre_order_active ?? false,
                    'pre_order_for_shop' => $request->pre_order_for_shop ?? false,
                    'shop_commission' => $request->shop_commission ?? 0,
                    'commission_type' => $request->commission_type ?? 'percentage',
                    'pre_order_policy' => $request->pre_order_policy ?? '',
                    'pre_order_refund_policy' => $request->pre_order_refund_policy ?? ''
                ]
            );
            if (!$preOrderSetting->pre_order_for_shop) {
                PreOrderProductRepository::query()->withoutGlobalScope(PreOderProduct::class)->where('shop_id', '!=', $rootShopId)->where('is_preorder', true)->update([
                    'is_active' => false
                ]);
            }
        });
    }
}
