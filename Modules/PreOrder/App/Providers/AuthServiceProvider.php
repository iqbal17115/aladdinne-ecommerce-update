<?php

namespace Modules\PreOrder\App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Modules\PreOrder\App\Models\PreOrderSetting;
use Modules\PreOrder\App\Policies\PreOrderPolicy;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        PreOrderSetting::class => PreOrderPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();
    }
}
