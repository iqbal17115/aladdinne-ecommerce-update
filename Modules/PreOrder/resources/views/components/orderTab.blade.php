<ul class="nav nav-pills reportModuleTab">
    <li class="nav-item">
        <a href="{{ route('shop.report.saleProduct') }}"
            class="nav-link {{ $reportType === 'saleProduct' ? 'active' : '' }}">{{ __('Sale Report') }}</a>
    </li>
    @hasPermission('shop.report.wishList')
        <li class="nav-item">
            <a href="{{ route('shop.report.wishList') }}"
                class="nav-link {{ $reportType === 'wishList' ? 'active' : '' }}">{{ __('Wishlist Report') }}</a>
        </li>
    @endhasPermission
    @hasPermission('shop.report.productSearch')
        <li class="nav-item">
            <a href="{{ route('shop.report.productSearch') }}"
                class="nav-link {{ $reportType === 'productSearch' ? 'active' : '' }}">{{ __('Search Report') }}</a>
        </li>
    @endhasPermission
    @hasPermission('shop.report.addToCart')
        <li class="nav-item">
            <a href="{{ route('shop.report.addToCart') }}"
                class="nav-link {{ $reportType === 'addToCart' ? 'active' : '' }}">{{ __('Add To Cart') }}</a>
        </li>
    @endhasPermission
</ul>
