@hasPermission([
    'shop.report.analytics',
    'shop.report.saleProduct',
    'shop.report.earningSummary',
    'shop.report.commissionList',
    'shop.report.payOut'
])
    <li>
        <a class="menu {{ request()->routeIs('shop.report.*', 'shop.purchaseReturn.*') ? 'active' : '' }}"
            data-bs-toggle="collapse" href="#reportMenu">
            <span>
                <img class="menu-icon" src="{{ asset('assets/icons-admin/chart-trend-up.svg') }}" alt="icon"
                    loading="lazy" />
                {{ __('Report') }}
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
        <div class="collapse dropdownMenuCollapse {{ $request->routeIs('shop.report.*', 'shop.purchaseReturn.*') ? 'show' : '' }}"
            id="reportMenu">
            <div class="listBar">
                @hasPermission('shop.report.analytics')
                    <a href="{{ route('shop.report.analytics') }}"
                        class="subMenu hasCount {{ request()->routeIs('shop.report.analytics') ? 'active' : '' }}">
                        {{ __('Analytics') }}
                    </a>
                @endhasPermission
                @hasPermission('shop.report.saleProduct')
                    <a href="{{ route('shop.report.saleProduct') }}"
                        class="subMenu hasCount {{ request()->routeIs('shop.report.saleProduct', 'shop.report.wishList', 'shop.report.productSearch', 'shop.report.addToCart') ? 'active' : '' }}">
                        {{ __('Report') }}
                    </a>
                @endhasPermission
                @hasPermission('shop.report.earningSummary')
                    <a href="{{ route('shop.report.earningSummary') }}"
                        class="subMenu hasCount {{ request()->routeIs('shop.report.earningSummary') ? 'active' : '' }}">
                        {{ __('Earning Summary') }}
                    </a>
                @endhasPermission
                @hasPermission('shop.report.commissionList')
                    <a href="{{ route('shop.report.commissionList') }}"
                        class="subMenu hasCount {{ request()->routeIs('shop.report.commissionList') ? 'active' : '' }}">
                        {{ __('Commission List') }}
                    </a>
                @endhasPermission
                @hasPermission('shop.report.payOut')
                    <a href="{{ route('shop.report.payOut') }}"
                        class="subMenu hasCount {{ request()->routeIs('shop.report.payOut') ? 'active' : '' }}">
                        {{ __('PayOut') }}
                    </a>
                @endhasPermission
            </div>
        </div>
    </li>
@endhasPermission
