@php
    $preOrderSetting = \Modules\PreOrder\App\Models\PreOrderSetting::first();
@endphp
@can('preOrderForShop', $preOrderSetting)
    @hasPermission([
        'shop.preOrder.analytics',
        'shop.preOrder.product.index',
        'shop.preOrder.product.create',
        'shop.preOrder.index',
        'shop.preOrder.setting'
    ])
        <li>
            <a class="menu {{ request()->routeIs('shop.preOrder.*') ? 'active' : '' }}" data-bs-toggle="collapse"
                href="#preOrderMenu">
                <span>
                    <img class="menu-icon" src="{{ asset('assets/icons-admin/delivery-cart-arrow-up-1.svg') }}" alt="icon"
                        loading="lazy" />
                    {{ __('Pre-orders') }}
                </span>
                @if (app()->environment('local'))
                    <span>
                        <i class="fas fa-gift"></i>
                        <img src="{{ asset('assets/icons-admin/caret-down.svg') }}" alt="icon" class="downIcon">
                    </span>
                @else
                    <img src="{{ asset('assets/icons-admin/caret-down.svg') }}" alt="icon" class="downIcon">
                @endif
            </a>
            <div class="collapse dropdownMenuCollapse {{ $request->routeIs('shop.preOrder.*') ? 'show' : '' }}"
                id="preOrderMenu">
                <div class="listBar">
                    @hasPermission('shop.preOrder.analytics')
                        <a href="{{ route('shop.preOrder.analytics') }}"
                            class="subMenu hasCount {{ request()->routeIs('shop.preOrder.analytics') ? 'active' : '' }}">
                            {{ __('Analytics') }}
                        </a>
                    @endhasPermission
                    @hasPermission('shop.preOrder.index')
                        <a href="{{ route('shop.preOrder.index') }}"
                            class="subMenu hasCount {{ request()->routeIs('shop.preOrder.index', 'shop.preOrder.show') ? 'active' : '' }}">
                            {{ __('Order List') }}
                        </a>
                    @endhasPermission
                    @hasPermission('shop.preOrder.product.index')
                        <a href="{{ route('shop.preOrder.product.index') }}"
                            class="subMenu hasCount {{ request()->routeIs('shop.preOrder.product.index') ? 'active' : '' }}">
                            {{ __('Product List') }}
                        </a>
                    @endhasPermission
                    @hasPermission('shop.preOrder.product.create')
                        <a href="{{ route('shop.preOrder.product.create') }}"
                            class="subMenu hasCount {{ request()->routeIs('shop.preOrder.product.create') ? 'active' : '' }}">
                            {{ __('Add Product') }}
                        </a>
                    @endhasPermission
                    @if (generaleSetting()?->shop_type != 'single')
                        @hasPermission('shop.preOrder.CommissionList')
                            <a href="{{ route('shop.preOrder.CommissionList') }}"
                                class="subMenu hasCount {{ request()->routeIs('shop.preOrder.CommissionList') ? 'active' : '' }}">
                                {{ __('Commission') }}
                            </a>
                        @endhasPermission
                    @endif
                    @hasPermission('shop.preOrder.profitReport')
                        <a href="{{ route('shop.preOrder.profitReport') }}"
                            class="subMenu hasCount {{ request()->routeIs('shop.preOrder.profitReport') ? 'active' : '' }}">
                            {{ __('Profit Report') }}
                        </a>
                    @endhasPermission
                    @can('onlyAdminShop', $preOrderSetting)
                        @hasPermission('shop.preOrder.setting')
                            <a href="{{ route('shop.preOrder.setting') }}"
                                class="subMenu hasCount {{ request()->routeIs('shop.preOrder.setting') ? 'active' : '' }}">
                                {{ __('Setting') }}
                            </a>
                        @endhasPermission
                    @endcan
                </div>
            </div>
        </li>
    @endhasPermission
@endcan
