<?php

namespace Modules\PreOrder\App\Policies;

use App\Models\User;
use Modules\PreOrder\App\Models\PreOrderSetting;

class PreOrderPolicy
{
    public function onlyAdminShop(User $user): bool
    {
        $shop = generaleSetting('shop');
        $rootShop = generaleSetting('rootShop');
        return $shop && $rootShop && $shop->id === $rootShop->id;
    }

    /**
     * Check preorder allowed for shop
     */
    public function preOrderForShop(User $user, PreOrderSetting $setting): bool
    {
        if ($this->onlyAdminShop($user)) {
            return true;
        }
        return (bool) $setting->pre_order_for_shop;
    }

    /**
     * Check preorder system active
     */
    public function preOrderActive(User $user, PreOrderSetting $setting): bool
    {
        return (bool) $setting->pre_order_active;
    }

    /**
     * Check if preorder is allowed for current shop
     */
    public function canUsePreOrder(User $user, PreOrderSetting $setting): bool
    {
        return $this->preOrderActive($user, $setting)
            && $this->preOrderForShop($user, $setting);
    }
}
