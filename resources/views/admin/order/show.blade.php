@extends('layouts.app')
@section('header-title', __('Order Details'))

@section('content')
    <div class="admin-order-show">
    <div class="row my-3 g-3">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between flex-column flex-md-row  gap-2 py-3">
                    <h4 class="card-title mb-0">{{ __('Order Details') }}</h4>
                    <div class="d-flex gap-2 flex-wrap order-detail-actions">
                        @hasPermission(['shop.order.attach.barcode'])
                            @if (module_exists('purchase'))
                                <button type="button" class="btn btn-info py-2.5" data-bs-toggle="modal"
                                    data-bs-target="#stockOutModal">
                                    {{ __('Attach Product Barcode') }}
                                </button>
                            @endif
                        @endhasPermission
                        <a href="{{ route('shop.payment-slip', $order->id) }}" target="_blank"
                            class="btn btn-success py-2.5">
                            <img src="{{ asset('assets/icons-admin/download-alt.svg') }}" alt="icon" loading="lazy"
                                width="20" />
                            {{ __('Payment Slip') }}
                        </a>
                        <a href="{{ route('shop.download-invoice', $order->id) }}" target="_blank"
                            class="btn btn-primary py-2.5">
                            <img src="{{ asset('assets/icons-admin/download-alt.svg') }}" alt="icon" loading="lazy"
                                width="20" />
                            {{ __('Download Invoice') }}
                        </a>
                        <button type="button" class="btn btn-warning " id="orderLocation" data-id="{{ $order->id }}"
                            data-bs-toggle="modal" data-bs-target="#orderLocationModal">
                            <i class="fa-solid fa-location-dot"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="d-flex gap-3 flex-wrap align-items-center order-summary-grid">
                        <div class="flex-grow-1 order-summary-column">
                            <div class="order-item">
                                <label class="label">{{ __('Order Id') }}:</label>
                                <span class="value">#{{ $order->prefix . $order->order_code }}</span>
                            </div>
                            <div class="order-item">
                                <label class="label">{{ __('Payment Status') }}:</label>
                                <span class="value">{{ $order->payment_status }}</span>
                            </div>
                            <div class="order-item">
                                <label class="label">{{ __('Payment Method') }}:</label>
                                <span class="value">{{ $order->payment_method }}</span>
                            </div>
                        </div>

                        <div class="item-divider"></div>

                        <div class="flex-grow-1 order-summary-column">
                            <div class="order-item">
                                <label class="label">{{ __('Order Status') }}:</label>
                                <span class="value">{{ $order->order_status }}</span>
                            </div>
                            <div class="order-item">
                                <label class="label">{{ __('Order Date') }}:</label>
                                <span class="value">{{ $order->created_at->format('M d, Y') }}</span>
                            </div>
                            <div class="order-item">
                                <label class="label">{{ __('Delivery Date') }}:</label>
                                <span
                                    class="value">{{ $order->delivery_date ? Carbon\Carbon::parse($order->delivery_date)->format('M d, Y') : '-' }}</span>
                            </div>
                        </div>
                    </div>

                    @if ($canEditItems)
                        @hasPermission('admin.order.items.update')
                            <form id="order-items-form" action="{{ route('admin.order.items.update', $order->id) }}" method="POST">
                                @csrf
                                @method('PUT')
                            </form>
                        @endhasPermission
                    @endif

                    <div class="table-responsive mt-4 mb-0">
                        <table class="table border-left-right order-products-table">
                            <thead>
                                <tr>
                                    <th>{{ __('SL') }}</th>
                                    <th>{{ __('Product') }}</th>
                                    @if ($businessModel == 'multi')
                                        <th>{{ __('Shop') }}</th>
                                    @endif
                                    <th>{{ __('Quantity') }}</th>
                                    <th>{{ __('Size') }}</th>
                                    <th>{{ __('Color') }}</th>
                                    <th>{{ __('Price') }}</th>
                                    <th class="text-end">{{ __('Total') }}</th>
                                    @if ($canEditItems && $order->products->count() > 1)
                                        @hasPermission('admin.order.items.update')
                                            <th>{{ __('Remove') }}</th>
                                        @endhasPermission
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($order->products as $key => $product)
                                    @php
                                        $lineId = $product->pivot->id;
                                        $linePrice = $product->pivot->price ?? ($product->discount_price > 0 ? $product->discount_price : $product->price);
                                        $selectedSizeId = $product->sizes->firstWhere('name', $product->pivot->size)?->id;
                                        $selectedColorId = $product->colors->firstWhere('name', $product->pivot->color)?->id;
                                    @endphp
                                    <tr>
                                        <td>{{ $key + 1 }}</td>
                                        <td>
                                            <div class="d-flex gap-1 align-items-center order-product-cell">
                                                <img src="{{ $product->thumbnail }}" alt="" width="40"
                                                    height="40" loading="lazy">
                                                <span class="order-product-name">{{ $product->name }}
                                                    @if (module_exists('purchase') && !empty($product->pivot->sku))
                                                        <span class="fw-bold">
                                                            #{{ __('SKU') }}:
                                                            <span class="text-primary">({{ $product->pivot->sku }})</span>
                                                        </span>
                                                    @endif
                                                </span>
                                            </div>
                                        </td>
                                        @if ($businessModel == 'multi')
                                            <td>{{ $product->shop?->name }}</td>
                                        @endif
                                        <td>
                                            @if ($canEditItems)
                                                @hasPermission('admin.order.items.update')
                                                    <input type="number" class="form-control form-control-sm" min="1"
                                                        name="items[{{ $product->pivot->id }}][quantity]"
                                                        value="{{ old('items.'.$product->pivot->id.'.quantity', $product->pivot->quantity) }}"
                                                        form="order-items-form" required aria-label="{{ __('Quantity') }}">
                                                @else
                                                    {{ $product->pivot->quantity }}
                                                @endhasPermission
                                            @else
                                                {{ $product->pivot->quantity }}
                                            @endif
                                        </td>
                                        <td>
                                            @if ($canEditItems && $product->sizes->isNotEmpty())
                                                @hasPermission('admin.order.items.update')
                                                    <select name="items[{{ $lineId }}][size_id]" form="order-items-form"
                                                        class="form-select form-select-sm" required aria-label="{{ __('Size') }}">
                                                        <option value="">{{ __('Choose size') }}</option>
                                                        @foreach ($product->sizes as $size)
                                                            <option value="{{ $size->id }}" @selected(old('items.'.$lineId.'.size_id', $selectedSizeId) == $size->id)>
                                                                {{ $size->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                @else
                                                    {{ $product->pivot->size ?? '-' }}
                                                @endhasPermission
                                            @else
                                                {{ $product->pivot->size ?? '-' }}
                                            @endif
                                        </td>
                                        <td>
                                            @if ($canEditItems && $product->colors->isNotEmpty())
                                                @hasPermission('admin.order.items.update')
                                                    <select name="items[{{ $lineId }}][color_id]" form="order-items-form"
                                                        class="form-select form-select-sm" required aria-label="{{ __('Color') }}">
                                                        <option value="">{{ __('Choose color') }}</option>
                                                        @foreach ($product->colors as $color)
                                                            <option value="{{ $color->id }}" @selected(old('items.'.$lineId.'.color_id', $selectedColorId) == $color->id)>
                                                                {{ $color->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                @else
                                                    {{ $product->pivot->color ?? '-' }}
                                                @endhasPermission
                                            @else
                                                {{ $product->pivot->color ?? '-' }}
                                            @endif
                                        </td>
                                        <td>
                                            @if ($canEditItems)
                                                @hasPermission('admin.order.items.update')
                                                    <input type="number" name="items[{{ $lineId }}][price]" form="order-items-form"
                                                        class="form-control form-control-sm order-line-price" min="0"
                                                        max="10000000" step="0.01" required
                                                        value="{{ old('items.'.$lineId.'.price', $linePrice) }}"
                                                        aria-label="{{ __('Unit price') }}">
                                                @else
                                                    {{ showCurrency($linePrice) }}
                                                @endhasPermission
                                            @else
                                                {{ showCurrency($linePrice) }}
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            {{ showCurrency($product->pivot->quantity * $linePrice) }}
                                        </td>
                                        @if ($canEditItems && $order->products->count() > 1)
                                            @hasPermission('admin.order.items.update')
                                                <td>
                                                    <label class="form-check d-inline-flex align-items-center gap-2 mb-0">
                                                        <input type="checkbox" class="form-check-input mt-0"
                                                            name="items[{{ $product->pivot->id }}][remove]" value="1"
                                                            form="order-items-form">
                                                        <span>{{ __('Remove') }}</span>
                                                    </label>
                                                </td>
                                            @endhasPermission
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if ($canEditItems && $availableProductsData->isNotEmpty())
                        @hasPermission('admin.order.items.update')
                            <div class="order-add-product-panel border-top mt-3 pt-3">
                                <h6 class="mb-3">{{ __('Add Product to Order') }}</h6>
                                <div class="row g-3 align-items-end">
                                    <div class="col-lg-5">
                                        <label for="order-add-product" class="form-label">{{ __('Product') }}</label>
                                        <select id="order-add-product" name="new_item[product_id]" form="order-items-form"
                                            class="form-select select2 order-add-product-select" style="width: 100%">
                                            <option value="">{{ __('Choose a product') }}</option>
                                            @foreach ($availableProductsData as $availableProduct)
                                                <option value="{{ $availableProduct['id'] }}"
                                                    @selected(old('new_item.product_id') == $availableProduct['id'])>
                                                    {{ $availableProduct['name'] }} ({{ __('Stock') }}: {{ $availableProduct['stock'] }})
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('new_item.product_id')
                                            <div class="text-danger small mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-lg-2" id="order-add-size-wrap" hidden>
                                        <label for="order-add-size" class="form-label">{{ __('Size') }}</label>
                                        <select id="order-add-size" name="new_item[size_id]" form="order-items-form"
                                            class="form-select" disabled>
                                            <option value="">{{ __('Choose size') }}</option>
                                        </select>
                                        @error('new_item.size_id')
                                            <div class="text-danger small mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-lg-2" id="order-add-color-wrap" hidden>
                                        <label for="order-add-color" class="form-label">{{ __('Color') }}</label>
                                        <select id="order-add-color" name="new_item[color_id]" form="order-items-form"
                                            class="form-select" disabled>
                                            <option value="">{{ __('Choose color') }}</option>
                                        </select>
                                        @error('new_item.color_id')
                                            <div class="text-danger small mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-lg-2">
                                        <label for="order-add-quantity" class="form-label">{{ __('Quantity') }}</label>
                                        <input id="order-add-quantity" type="number" name="new_item[quantity]"
                                            form="order-items-form" class="form-control" min="1"
                                            value="{{ old('new_item.quantity', 1) }}" disabled>
                                        @error('new_item.quantity')
                                            <div class="text-danger small mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-lg-1">
                                        <div class="small text-muted" id="order-add-product-stock" role="status"></div>
                                        <div class="small fw-semibold" id="order-add-product-price"></div>
                                    </div>
                                </div>
                            </div>
                        @endhasPermission
                    @endif

                    @if ($canEditItems)
                        @hasPermission('admin.order.items.update')
                            @error('items')
                                <div class="alert alert-danger mt-3 mb-0">{{ $message }}</div>
                            @enderror
                            <div class="d-flex justify-content-end mt-3">
                                <button type="submit" class="btn btn-primary" form="order-items-form">
                                    {{ __('Save Order Changes') }}
                                </button>
                            </div>
                        @endhasPermission
                    @endif

                    <div class="max-300 ms-auto d-flex flex-column gap-1 order-total-summary">
                        <div class="d-flex align-items-center justify-content-between gap-2">
                            <div>{{ __('Sub Total') }}</div>
                            <div>{{ showCurrency($order->total_amount) }}</div>
                        </div>

                        <div class="d-flex align-items-center justify-content-between gap-2">
                            <div>{{ __('Coupon Discount') }}</div>
                            <div>{{ showCurrency($order->coupon_discount) }}</div>
                        </div>

                        <div class="d-flex align-items-center justify-content-between gap-2">
                            <div>{{ __('Delivery Charge') }}</div>
                            @if ($canEditItems)
                                @hasPermission('admin.order.items.update')
                                    <div class="order-delivery-charge-control">
                                        @php($currencySetting = generaleSetting('setting'))
                                        <div class="input-group input-group-sm">
                                            @if ($currencySetting?->currency_position !== 'suffix')
                                                <span class="input-group-text">{{ $currencySetting?->currency ?? '$' }}</span>
                                            @endif
                                            <input type="number" name="delivery_charge" form="order-items-form"
                                                class="form-control text-end" min="0" max="10000000"
                                                step="0.01" required
                                                value="{{ old('delivery_charge', number_format((float) $order->delivery_charge, 2, '.', '')) }}"
                                                aria-label="{{ __('Delivery Charge') }}">
                                            @if ($currencySetting?->currency_position === 'suffix')
                                                <span class="input-group-text">{{ $currencySetting?->currency ?? '$' }}</span>
                                            @endif
                                        </div>
                                        @error('delivery_charge')
                                            <div class="text-danger small mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                @else
                                    <div>{{ showCurrency($order->delivery_charge) }}</div>
                                @endhasPermission
                            @else
                                <div>{{ showCurrency($order->delivery_charge) }}</div>
                            @endif
                        </div>

                        <div class="d-flex align-items-center justify-content-between gap-2">
                            <div>{{ __('VAT & Tax') }}</div>
                            <div>{{ showCurrency($order->tax_amount) }}</div>
                        </div>

                        <div class="d-flex align-items-center justify-content-between gap-2 border-top pt-1 mt-1">
                            <div class="fw-bold">{{ __('Grand Total') }}</div>
                            <div class="fw-bold">{{ showCurrency($order->payable_amount) }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!--##### Customer Info #####-->
            <div class="mt-3 card">
                <h5 class="fz-16 border-bottom px-3 py-12 m-0">{{ __('Customer Info') }}</h5>

                <div class="border-bottom px-3 py-2 d-flex align-items-center gap-3 customer-info-row">
                    <span class="text-color">{{ __('Name') }}: </span>
                    <span class="fw-medium">{{ $order->customer?->user?->name }}</span>
                </div>
                <div class="px-3 py-2 d-flex align-items-center gap-3 customer-info-row">
                    <span class="text-color">{{ __('Phone') }}: </span>
                    <span class="fw-medium">{{ $order->customer?->user?->phone }}</span>
                </div>
            </div>

        </div>

        <div class="col-lg-4">
            <!--##### Order & Shipping Info #####-->
            <div class="card">
                <h5 class="fz-18 border-bottom p-3 m-0">{{ __('Order & Shipping Info') }}</h5>

                <div class="px-3 py-2 d-flex justify-content-between align-items-center flex-wrap gap-2 border-bottom info-row">
                    <div class="text-color">{{ __('Change Order Status') }}</div>
                    <div class="dropdown info-row-control">
                        <a class="btn border text-start dropdown-toggle" href="#" role="button"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            {{ $order->order_status->value }}
                        </a>
                        @if ($order->order_status->value != 'Delivered' && $order->order_status->value != 'Cancelled')
                            @hasPermission(['admin.order.status.change'])
                                <ul class="dropdown-menu order-status">
                                    @foreach ($orderStatus as $status)
                                        <li>
                                            <a class="dropdown-item @if (in_array($status->value, ['Delivered', 'Cancelled'])) OrderStatusConfirm @endif"
                                                href="{{ route('admin.order.status.change', $order->id) }}?status={{ $status->value }}">
                                                {{ __($status->value) }}
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            @endhasPermission
                        @endif
                    </div>
                </div>

                <div class="border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2 p-3 info-row">
                    <div class="text-color">{{ __('Payment Status') }}</div>
                    <div class="d-flex align-items-center gap-1 info-row-control">
                        <span>{{ $order->payment_status }}</span>
                        @hasPermission('admin.order.payment.status.toggle')
                            <label class="switch mb-0">
                                <a href="{{ route('admin.order.payment.status.toggle', $order->id) }}">
                                    <input type="checkbox" {{ $order->payment_status->value == 'Paid' ? 'checked' : '' }}>
                                    <span class="slider round"></span>
                                </a>
                            </label>
                        @endhasPermission
                    </div>
                </div>

                @hasPermission('admin.rider.assign.order')
                    @if ($order->order_status->value != 'Pending' && ! $courierTracking)
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 p-3 info-row">
                            <div class="fw-medium text-color">{{ __('Assign Rider') }}</div>
                            <div class="d-flex align-items-center gap-1 info-row-control">

                                @if ($order->driverOrder)
                                    <span>{{ $order->driverOrder->driver?->user?->fullName }}</span>
                                @else
                                    <button class="btn btn-outline-primary" data-bs-toggle="modal"
                                        data-bs-target="#assignRider">
                                        <img src="{{ asset('assets/icons-admin/truck-fill.svg') }}" alt="icon"
                                            loading="lazy" />
                                        {{ __('Assign') }}
                                    </button>
                                @endif

                            </div>
                        </div>
                    @endif
                @endhasPermission

                @if (config('steadfast.active'))
                    @hasPermission('admin.order.assign.courier')
                        @if (! in_array($order->order_status->value, ['Pending', 'Cancelled', 'Delivered']) && ! $order->driverOrder)
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 p-3 info-row">
                                <div class="fw-medium text-color">{{ __('Courier') }}</div>
                                <div class="d-flex align-items-center gap-1 info-row-control">
                                    @if ($courierTracking)
                                        <span class="badge rounded-pill text-bg-primary">
                                            {{ $courierTracking->courier_name }} &mdash; {{ $courierTracking->status }}
                                        </span>
                                    @else
                                        <button class="btn btn-outline-primary" data-bs-toggle="modal"
                                            data-bs-target="#assignCourier">
                                            <img src="{{ asset('assets/icons-admin/truck-fill.svg') }}" alt="icon"
                                                loading="lazy" />
                                            {{ __('Send to SteadFast') }}
                                        </button>
                                    @endif
                                </div>
                            </div>
                        @endif
                    @endhasPermission
                @endif
            </div>

            <!--##### Shipping Address #####-->
            <div class="card mt-3">
                <h5 class="fz-18 border-bottom p-3 m-0">{{ __('Shipping Address') }}</h5>

                <div class="border-bottom d-flex align-items-center justify-content-between gap-2 px-3 py-12 shipping-row">
                    <span class="text-color">{{ __('Name') }}: </span>
                    <span class="fw-medium">{{ $order->address?->name }}</span>
                </div>
                <div class="border-bottom d-flex align-items-center justify-content-between gap-2 px-3 py-12 shipping-row">
                    <span class="text-color">{{ __('Phone') }}: </span>
                    <span class="fw-medium">{{ $order->order_phone ?? $order->address?->phone }}</span>
                </div>
                <div class="border-bottom d-flex align-items-center justify-content-between gap-2 px-3 py-12 shipping-row">
                    <span class="text-color">{{ __('Address Type') }}: </span>
                    <span class="fw-medium">{{ $order->address?->address_type }}</span>
                </div>
                <div class="border-bottom d-flex align-items-center justify-content-between gap-2 px-3 py-12 shipping-row">
                    <span class="text-color">{{ __('District / Area') }}: </span>
                    <span class="fw-medium">{{ $order->order_area ?? 'N/A' }}</span>
                </div>
                <div class="border-bottom d-flex align-items-center justify-content-between gap-2 px-3 py-12 shipping-row">
                    <span class="text-color">{{ __('Thana') }}: </span>
                    <span class="fw-medium">{{ $order->order_thana ?? 'N/A' }}</span>
                </div>
                <div class="border-bottom d-flex align-items-center justify-content-between gap-2 px-3 py-12 shipping-row">
                    <span class="text-color">{{ __('Address Line') }}: </span>
                    <span class="fw-medium">{{ $order->order_address_line ?? $order->address?->address_line }}</span>
                </div>
            </div>

            @hasPermission('admin.order.delivery.update')
                <div class="card mt-3">
                    <h5 class="fz-18 border-bottom p-3 m-0">{{ __('Edit Delivery Details') }}</h5>
                    <form action="{{ route('admin.order.delivery.update', $order->id) }}" method="POST" class="p-3">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="order_phone" class="form-label">{{ __('Phone') }}</label>
                            <input type="tel" id="order_phone" name="order_phone" class="form-control"
                                value="{{ old('order_phone', $order->order_phone ?? $order->address?->phone) }}" maxlength="50">
                            @error('order_phone')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="order_address_line" class="form-label">{{ __('Address Line') }}</label>
                            <textarea id="order_address_line" name="order_address_line" class="form-control" rows="2" maxlength="2000">{{ old('order_address_line', $order->order_address_line ?? $order->address?->address_line) }}</textarea>
                            @error('order_address_line')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="order_area" class="form-label">{{ __('District / Area') }}</label>
                            <input type="text" id="order_area" name="order_area" class="form-control"
                                value="{{ old('order_area', $order->order_area) }}" maxlength="255">
                            @error('order_area')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="order_thana" class="form-label">{{ __('Thana') }}</label>
                            <input type="text" id="order_thana" name="order_thana" class="form-control"
                                value="{{ old('order_thana', $order->order_thana) }}" maxlength="255">
                            @error('order_thana')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="internal_note" class="form-label">{{ __('Internal Note') }}</label>
                            <textarea id="internal_note" name="internal_note" class="form-control" rows="4" maxlength="5000">{{ old('internal_note', $order->internal_note) }}</textarea>
                            @error('internal_note')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-primary">{{ __('Save Changes') }}</button>
                    </form>
                </div>
            @endhasPermission

        </div>
    </div>
    </div>

    <!-- Assign Rider Modal -->
    <form action="{{ route('admin.rider.assign.order', $order->id) }}" method="POST">
        @csrf
        <div class="modal fade" id="assignRider">
            <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h3 class="modal-title fs-5">{{ __('Select a rider') }}</h3>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="d-flex gap-2 flex-column">
                            @foreach ($riders as $rider)
                                <div class="w-100">
                                    <input type="radio" name="rider" value="{{ $rider->id }}"
                                        id="rider{{ $rider->id }}" class="btn-check">
                                    <label for="rider{{ $rider->id }}" class="btn riderSelectBtn">
                                        <div>
                                            <img src="{{ $rider->user->thumbnail }}" alt="profile"
                                                class="profilePhoto" />
                                            <span class="riderName">
                                                {{ $rider->user->fullName }}
                                            </span>
                                        </div>
                                        <div class="d-flex gap-1 align-items-center">
                                            <span class="text-muted inCompleted">
                                                {{ __('Incomplete Orders') }}:
                                            </span>
                                            <span class="totalOrders">{{ $rider->incompleteOrders() }}</span>
                                        </div>

                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">
                            {{ __('Assign Now') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <!-- Send to SteadFast Modal -->
    @if (config('steadfast.active'))
        <form action="{{ route('admin.order.assign.courier', $order->id) }}" method="POST">
            @csrf
            <div class="modal fade" id="assignCourier">
                <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                    <div class="modal-content steadfast-modal">
                        <div class="modal-header border-0 pb-0">
                            <div class="d-flex align-items-center gap-2">
                                <span class="steadfast-modal-icon">
                                    <img src="{{ asset('assets/icons-admin/truck-fill.svg') }}" alt="icon" loading="lazy" />
                                </span>
                                <h3 class="modal-title fs-5 m-0">{{ __('Send to SteadFast') }}</h3>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body pt-2">
                            <p class="text-muted mb-3">
                                {{ __('This order will be booked with SteadFast courier for delivery. Please review before confirming.') }}
                            </p>
                            <div class="steadfast-summary">
                                <div class="steadfast-summary-row">
                                    <span class="text-color">{{ __('Recipient') }}</span>
                                    <span class="fw-medium">{{ $order->address?->name }}</span>
                                </div>
                                <div class="steadfast-summary-row">
                                    <span class="text-color">{{ __('Phone') }}</span>
                                    <span class="fw-medium">{{ $order->address?->phone }}</span>
                                </div>
                                <div class="steadfast-summary-row">
                                    <span class="text-color">{{ __('Address') }}</span>
                                    <span class="fw-medium text-end">{{ $order->address?->address_line }}{{ $order->order_thana ? ', '.$order->order_thana : '' }}{{ $order->order_area ? ', '.$order->order_area : '' }}</span>
                                </div>
                                <div class="steadfast-summary-row">
                                    <span class="text-color">{{ __('COD Amount') }}</span>
                                    <span class="fw-medium">{{ number_format($order->payable_amount ?? $order->total_amount, 2) }}</span>
                                </div>
                                <div class="steadfast-summary-row">
                                    <span class="text-color">{{ __('Delivery Type') }}</span>
                                    <span class="fw-medium">
                                        {{ (int) config('steadfast.default_delivery_type') === 1 ? __('Point Delivery') : __('Home Delivery') }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer border-0 pt-0">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                            <button type="submit" class="btn btn-primary">
                                <img src="{{ asset('assets/icons-admin/truck-fill.svg') }}" alt="icon" loading="lazy" />
                                {{ __('Confirm & Send') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        <style>
            .steadfast-modal-icon {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                width: 40px;
                height: 40px;
                border-radius: 50%;
                background: rgba(13, 110, 253, 0.1);
            }
            .steadfast-modal-icon img {
                width: 20px;
                height: 20px;
            }
            .steadfast-summary {
                border: 1px solid var(--bs-border-color, #e9ecef);
                border-radius: 10px;
                padding: 14px 16px;
                background: rgba(0, 0, 0, 0.015);
            }
            .steadfast-summary-row {
                display: flex;
                align-items: flex-start;
                justify-content: space-between;
                gap: 12px;
                padding: 8px 0;
                border-bottom: 1px dashed var(--bs-border-color, #e9ecef);
            }
            .steadfast-summary-row:last-child {
                border-bottom: none;
                padding-bottom: 0;
            }
            .steadfast-summary-row:first-child {
                padding-top: 0;
            }
        </style>
    @endif

    @if (module_exists('purchase'))
        <form id="scannerForm" method="POST" action="{{ route('shop.order.attach.barcode') }}">
            @csrf
            <input type="hidden" name="order_id" value="{{ $order->id }}" />
            <div class="modal fade" id="stockOutModal">
                <div class="modal-dialog modal-lg modal-dialog-scrollable">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">{{ __('Scan Barcode for attachment') }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="barcodeInput"
                                    class="form-label">{{ __('Enter Barcode Manually / Scan Barcode') }}</label>
                                <div class="input-group">
                                    <input type="text" id="barcodeInput" class="form-control"
                                        placeholder="Type barcode and press Enter" autofocus />
                                </div>
                            </div>
                            <h6>{{ __('Scanned Products') }}:</h6>
                            <div id="scanner-container" class="mb-3 p-2"></div>
                        </div>
                        <div class="modal-footer justify-content-between">
                            <button type="button" class="btn btn-outline-secondary"
                                data-bs-dismiss="modal">{{ __('Close') }}</button>
                            <button type="submit" class="btn btn-primary py-2.5 px-4" id="scanSubmit">
                                {{ __('Confirm Submit') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    @endif
    <!--Order Modal -->
    <div class="modal fade" id="orderLocationModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
        aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="staticBackdropLabel">Order Location</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="map" style="height: 70vh; width: 100%;"></div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('css')
    <style>
        .admin-order-show .card,
        .admin-order-show .modal-content {
            max-width: 100%;
        }

        .admin-order-show .order-detail-actions {
            justify-content: flex-end;
        }

        .admin-order-show .order-detail-actions .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .admin-order-show .order-summary-column {
            min-width: 0;
        }

        .admin-order-show .order-products-table {
            min-width: 760px;
        }

        .admin-order-show .order-products-table td,
        .admin-order-show .order-products-table th {
            vertical-align: middle;
        }

        .admin-order-show .order-product-cell {
            min-width: 220px;
        }

        .admin-order-show .order-product-name {
            overflow-wrap: anywhere;
        }

        .admin-order-show .order-total-summary {
            width: 100%;
        }

        .admin-order-show .order-delivery-charge-control {
            flex: 0 0 170px;
            max-width: 55%;
        }

        .dropdown-menu.order-status {
            min-width: 200px;
            padding: 8px;
            border: 1px solid #e5e5e5;
            box-shadow: 0 0 10px #e5e5e5;
        }

        .dropdown-menu.order-status .dropdown-item {
            border-bottom: 1px solid #f1f1f1;
        }

        .app-theme-dark .dropdown-menu.order-status {
            border: 1px solid #343a40;
            box-shadow: 0 0 10px #343a40;
        }

        .app-theme-dark .dropdown-menu.order-status .dropdown-item {
            border-bottom: 1px solid #343a40;
        }

        .max-300 {
            max-width: 340px;
        }

        .min-w-200 {
            min-width: 200px;
            display: inline;
        }

        .item-divider {
            height: 80px;
            width: 1px;
            background: #e5e5e5;
            margin: 0 20px;
        }

        .app-theme-dark .item-divider {
            background: #343a40;
        }

        .order-item {
            display: flex;
            gap: 10px;
            align-items: center;
            justify-content: space-between;
            font-size: 14px;
            margin-bottom: 10px;
        }

        .order-item:last-child {
            margin-bottom: 0;
        }

        .order-item .label {
            color: #687387;
            line-height: 22px;
        }

        .app-theme-dark .order-item .label {
            color: #8f96a6;
        }

        .order-item .value {
            line-height: 22px;
            font-weight: 500;
            color: #000;
        }

        .app-theme-dark .order-item .value {
            color: #fff;
        }

        @media (max-width: 991.98px) {
            .admin-order-show .card-header {
                align-items: flex-start !important;
            }

            .admin-order-show .order-detail-actions {
                justify-content: flex-start;
                width: 100%;
            }

            .admin-order-show .order-products-table {
                min-width: 680px;
            }

            .admin-order-show .info-row,
            .admin-order-show .shipping-row {
                align-items: flex-start !important;
            }
        }

        @media (max-width: 768px) {
            .item-divider {
                display: none;
            }

            .admin-order-show .card-header {
                padding: 1rem !important;
            }

            .admin-order-show .card-body {
                padding: 1rem;
            }

            .admin-order-show .order-detail-actions .btn {
                width: 100%;
            }

            .admin-order-show .order-summary-grid {
                gap: 1rem !important;
            }

            .admin-order-show .order-item {
                flex-direction: column;
                align-items: flex-start;
            }

            .admin-order-show .order-item .value {
                width: 100%;
                overflow-wrap: anywhere;
            }

            .admin-order-show .order-products-table {
                min-width: 620px;
            }

            .admin-order-show .customer-info-row,
            .admin-order-show .info-row,
            .admin-order-show .shipping-row {
                flex-direction: column;
                align-items: flex-start !important;
                gap: 6px !important;
            }

            .admin-order-show .info-row-control {
                width: 100%;
                justify-content: space-between;
            }

            .admin-order-show .info-row-control .btn,
            .admin-order-show .info-row-control .dropdown-toggle {
                max-width: 100%;
            }

            .admin-order-show #map {
                height: 55vh !important;
            }
        }

        @media (max-width: 575.98px) {
            .admin-order-show .row.my-3 {
                margin-top: 0.75rem !important;
                margin-bottom: 0.75rem !important;
            }

            .admin-order-show .card-title {
                font-size: 1.1rem;
            }

            .admin-order-show .order-detail-actions {
                gap: 0.75rem !important;
            }

            .admin-order-show .order-products-table {
                min-width: 560px;
            }

            .admin-order-show .max-300 {
                max-width: 100%;
            }

            .admin-order-show .dropdown-menu.order-status {
                min-width: 180px;
            }

            .admin-order-show .modal-dialog {
                margin: 0.75rem;
            }

            .admin-order-show #map {
                height: 50vh !important;
            }
        }
    </style>
@endpush
@push('scripts')
    <script>
        $(document).ready(function() {
            $(".dropdown-menu").on("click", ".OrderStatusConfirm", function(e) {
                e.preventDefault();
                e.stopImmediatePropagation();

                const url = $(this).attr("href");
                const statusName = $(this).text().trim();

                Swal.fire({
                    title: "Are you sure?",
                    text: `Do you really want to mark this order as ${statusName}?`,
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#3085d6",
                    cancelButtonColor: "#d33",
                    confirmButtonText: "Yes, proceed!"
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = url;
                    }
                });
            });
        });
    </script>

    <script>
        @if ($canEditItems && $availableProductsData->isNotEmpty())
            const availableOrderProducts = @json($availableProductsData->values());
            const orderProductSelect = document.getElementById('order-add-product');
            const orderSizeSelect = document.getElementById('order-add-size');
            const orderColorSelect = document.getElementById('order-add-color');
            const orderQuantityInput = document.getElementById('order-add-quantity');
            const orderStockPreview = document.getElementById('order-add-product-stock');
            const orderPricePreview = document.getElementById('order-add-product-price');
            const sizeField = document.getElementById('order-add-size-wrap');
            const colorField = document.getElementById('order-add-color-wrap');
            const currencySymbol = @json(generaleSetting('setting')?->currency ?? '$');
            const currencyPosition = @json(generaleSetting('setting')?->currency_position ?? 'prefix');
            const availableStockLabel = @json(__('Available stock'));
            const unitPriceLabel = @json(__('Unit price'));
            const previousProductId = @json(old('new_item.product_id'));
            const previousSizeId = @json(old('new_item.size_id'));
            const previousColorId = @json(old('new_item.color_id'));

            $('#order-add-product').select2({
                width: '100%',
                placeholder: @json(__('Choose a product')),
                allowClear: true,
            });
            let loadedProductId = null;

            function setVariantOptions(select, wrapper, options, placeholder, previousValue) {
                select.replaceChildren(new Option(placeholder, ''));
                options.forEach((option) => {
                    select.add(new Option(option.name, option.id));
                });
                wrapper.hidden = options.length === 0;
                select.disabled = options.length === 0;
                select.required = options.length > 0;

                if (previousValue && options.some((option) => String(option.id) === String(previousValue))) {
                    select.value = String(previousValue);
                }
            }

            function selectedProduct() {
                const product = availableOrderProducts.find((item) => String(item.id) === orderProductSelect.value);
                return product || null;
            }

            function updateNewProductSelection() {
                const product = selectedProduct();
                if (!product) {
                    orderSizeSelect.replaceChildren(new Option(@json(__('Choose size')), ''));
                    orderColorSelect.replaceChildren(new Option(@json(__('Choose color')), ''));
                    orderSizeSelect.disabled = true;
                    orderColorSelect.disabled = true;
                    orderSizeSelect.required = false;
                    orderColorSelect.required = false;
                    sizeField.hidden = true;
                    colorField.hidden = true;
                    orderQuantityInput.disabled = true;
                    orderQuantityInput.removeAttribute('max');
                    orderStockPreview.textContent = '';
                    orderPricePreview.textContent = '';
                    loadedProductId = null;
                    return;
                }

                if (loadedProductId === String(product.id)) {
                    updateNewProductPreview();
                    return;
                }

                const restorePreviousSelection = loadedProductId === null
                    && String(product.id) === String(previousProductId);
                setVariantOptions(
                    orderSizeSelect,
                    sizeField,
                    product.sizes,
                    @json(__('Choose size')),
                    restorePreviousSelection ? previousSizeId : null,
                );
                setVariantOptions(
                    orderColorSelect,
                    colorField,
                    product.colors,
                    @json(__('Choose color')),
                    restorePreviousSelection ? previousColorId : null,
                );
                orderQuantityInput.disabled = false;
                orderQuantityInput.max = product.stock;
                if (!restorePreviousSelection || !orderQuantityInput.value || Number(orderQuantityInput.value) < 1) {
                    orderQuantityInput.value = 1;
                }
                loadedProductId = String(product.id);
                updateNewProductPreview();
            }

            function updateNewProductPreview() {
                const product = selectedProduct();
                if (!product) {
                    return;
                }

                const quantity = Number(orderQuantityInput.value) || 1;
                const size = product.sizes.find((item) => String(item.id) === orderSizeSelect.value);
                const color = product.colors.find((item) => String(item.id) === orderColorSelect.value);
                const isFlashSalePrice = product.sale_price !== null && product.sale_remaining >= quantity;
                const basePrice = isFlashSalePrice ? Number(product.sale_price) : Number(product.base_price);
                const unitPrice = basePrice + Number(size?.price || 0) + Number(color?.price || 0);
                const formattedPrice = new Intl.NumberFormat(undefined, {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2,
                }).format(unitPrice);
                const priceText = currencyPosition === 'suffix'
                    ? `${formattedPrice}${currencySymbol}`
                    : `${currencySymbol}${formattedPrice}`;

                orderStockPreview.textContent = `${availableStockLabel}: ${product.stock}${product.unit ? ` ${product.unit}` : ''}`;
                orderPricePreview.textContent = `${unitPriceLabel}: ${priceText}`;
            }

            $('#order-add-product').on('change', updateNewProductSelection);
            orderSizeSelect.addEventListener('change', updateNewProductPreview);
            orderColorSelect.addEventListener('change', updateNewProductPreview);
            orderQuantityInput.addEventListener('input', updateNewProductPreview);
            updateNewProductSelection();
        @endif
    </script>

    <script>
        let map;
        let riderMarker;
        let routingControl;
        let trackingInterval = null;
        let riderId = @json($order->driverOrder->driver_id ?? null);


        const orderStatus = @json($order->order_status);


        const orderLat = {{ $order->address->latitude ?? 0 }};
        const orderLng = {{ $order->address->longitude ?? 0 }};

        const blockedStatuses = ['Delivered', 'Cancelled'];

        function canShowRiderLocation() {
            return riderId && !blockedStatuses.includes(orderStatus);
        }


        function initMap(riderLat, riderLng) {

            map = L.map('map').setView([orderLat, orderLng], 14);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(map);

            const orderIcon = L.icon({
                iconUrl: '{{ asset('assets/icons/home.png') }}',
                iconSize: [35, 35],
                iconAnchor: [17, 35],
                popupAnchor: [0, -30],
                shadowUrl: null
            });

            L.marker([orderLat, orderLng], {
                    icon: orderIcon
                })
                .addTo(map)
                .bindPopup('Customer Location')
                .openPopup();

            if (!canShowRiderLocation()) {
                return;
            }

            const riderIcon = L.icon({
                iconUrl: '{{ asset('assets/icons/pin-map.png') }}',
                iconSize: [35, 35],
                iconAnchor: [17, 35],
                popupAnchor: [0, -30],
                shadowUrl: null
            });

            riderMarker = L.marker([riderLat, riderLng], {
                    icon: riderIcon
                })
                .addTo(map);

            routingControl = L.Routing.control({
                waypoints: [
                    L.latLng(riderLat, riderLng),
                    L.latLng(orderLat, orderLng)
                ],
                addWaypoints: false,
                draggableWaypoints: false,
                fitSelectedRoutes: true,
                show: false,
                createMarker: function() {
                    return null;
                }
            }).addTo(map);
        }

        function subscribeToRiderLocation(riderId) {

            if (!canShowRiderLocation() || !riderMarker) return;
            channel = pusher.subscribe('rider-location.' + riderId);

            channel.bind('rider.location.updated', function(data) {

                if (!riderMarker || data.location.driver_id !== riderId) {
                    return;
                }

                const latitude = data.location.latitude;
                const longitude = data.location.longitude;

                moveMarkerSmooth(riderMarker, latitude, longitude, 5000);

                // riderMarker.setLatLng([latitude, longitude]);
                routingControl.setWaypoints([
                    L.latLng(latitude, longitude),
                    L.latLng(orderLat, orderLng)
                ]);
                map.panTo([latitude, longitude], {
                    animate: true
                });
            });
        }

        $(document).on('click', '#orderLocation', function() {

            $('#orderLocationModal').modal('show');

            $('#orderLocationModal').one('shown.bs.modal', function() {

                if (map) map.remove();

                initMap(orderLat, orderLng);

                setTimeout(() => map.invalidateSize(), 300);


                if (!canShowRiderLocation() || !riderId) {
                    return;
                }

                // Rider exists → fetch live location
                $.ajax({
                    url: "{{ route('admin.rider.location', ':id') }}".replace(':id', riderId),
                    success: function(res) {

                        if (!res?.data?.location) return;

                        let {
                            latitude,
                            longitude
                        } = res.data.location;

                        riderMarker.setLatLng([latitude, longitude]);

                        routingControl.setWaypoints([
                            L.latLng(latitude, longitude),
                            L.latLng(orderLat, orderLng)
                        ]);
                        // Live tracking
                        subscribeToRiderLocation(riderId);
                    }
                });
            });
        });


        $('#orderLocationModal').on('hidden.bs.modal', function() {
            if (trackingInterval) {
                clearInterval(trackingInterval);
                trackingInterval = null;
            }

            if (map) {
                map.remove();
                map = null;
            }
        });
    </script>
@endpush
@if (module_exists('purchase'))
    @push('scripts')
        <script src="https://cdnjs.cloudflare.com/ajax/libs/quagga/0.12.1/quagga.min.js"></script>
        <script>
            // scanner script
            let scannedBarcodes = new Set();
            let modal = document.getElementById("stockOutModal");

            function addScannedBarcode(barcode) {
                if (!scannedBarcodes.has(barcode)) {
                    fetchProductsBySku(barcode);

                    scannedBarcodes.add(barcode);
                    $('#barcodeInput').val('').focus();
                } else {
                    $('#barcodeInput').val('').focus();
                }
            }

            function absProductAssign(product_id) {
                $('#assignProductId').val(product_id);
                $('#assignRider').modal('show');
            }

            function fetchProductsBySku(sku) {
                $.ajax({
                    url: "{{ route('shop.order.fetch.products') }}",
                    type: "post",
                    data: {
                        sku: sku,
                        _token: "{{ csrf_token() }}",
                        order_id: "{{ $order->id }}"
                    },
                    success: function(response) {
                        let product = response.data.product;
                        let scannerContainer = document.getElementById("scanner-container");

                        if ($(`#scanned-product${product.id}`).length == 0) {

                            let html = `
                        <div class="w-100 border rounded p-4 shadow-sm" id="scanned-product${product.id}">
                            <div class="d-flex gap-1 align-items-center w-100 mb-1">
                                <div class="product-image">
                                    <img src="${product.thumbnail}" alt="thumbnail" loading="lazy" />
                                </div>
                                <div class="product-info">
                                    <div class="product-name">${product.name}</div>
                                </div>
                            </div>
                            <table class="table mt-1 w-100 border-left-right">
                                <thead>
                                    <tr>
                                        <th class="py-1">Barcode</th>
                                    </tr>
                                </thead>
                                <tbody id="scannedProduct${product.id}">
                                    <tr style="display: table-row !important">
                                        <td>${product.barcode}</td>
                                        <td>
                                            <input type="hidden" name="scanned_barcodes[]" value="${product.barcode}" />
                                        </td>
                                    </tr>
                                </tbody>
                            </table>

                        </div>`;

                            scannerContainer.insertAdjacentHTML('afterbegin', html);
                        } else {
                            let table = document.getElementById(`scannedProduct${product.id}`);
                            table.insertAdjacentHTML('afterbegin',
                                `<tr style="display: table-row !important">
                                <td>
                                    ${product.barcode}
                                    <input type="hidden" name="scanned_barcodes[]" value="${product.barcode}" />
                                </td>
                            </tr>`);
                        }
                    },
                    error: function(xhr, status, error) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: xhr.responseJSON.message
                        });
                    }
                })
            }

            // Handle manual barcode input
            document.getElementById("barcodeInput").addEventListener("keypress", function(event) {
                if (event.key === "Enter") {
                    event.preventDefault();
                    let barcode = this.value.trim();
                    if (barcode) {
                        addScannedBarcode(barcode);
                    }
                }
            });

            // Start QuaggaJS when modal opens
            function startScannerQuaggaJS() {
                Quagga.init({
                    inputStream: {
                        name: "Live",
                        type: "LiveStream",
                        target: document.getElementById("scanner-container"),
                        constraints: {
                            width: 640,
                            height: 300,
                            facingMode: "environment" // Rear camera (scanner gun)
                        }
                    },
                    decoder: {
                        readers: ["code_128_reader", "ean_reader", "ean_8_reader"]
                    }
                }, function(err) {
                    if (err) {
                        console.error(err);
                        return;
                    }
                    Quagga.start();
                });

                Quagga.onDetected(function(result) {
                    let barcode = result.codeResult.code;
                    addScannedBarcode(barcode);
                });
            }

            // Stop QuaggaJS when modal closes
            modal.addEventListener("hidden.bs.modal", function() {
                modal.setAttribute("aria-hidden", "true");
                modal.removeAttribute("aria-modal");
                Quagga.stop();
            });

            modal.addEventListener("shown.bs.modal", function() {
                setTimeout(function() {
                    document.getElementById("barcodeInput").focus();
                });
            })

            function scannerBarcode() {

                scannedBarcodes = new Set();

                $('#scannerModal').modal('show');
                modal.removeAttribute("aria-hidden");
                modal.setAttribute("aria-modal", "true");

                setTimeout(function() {
                    document.getElementById("barcodeInput").focus();
                }, 200);

                $('#scanner-container').empty();
                startScannerQuaggaJS();

                selectedOptions.each(function() {
                    var barcode = $(this).val();
                    addScannedBarcode(barcode);
                });
            }
        </script>
    @endpush
@endif
