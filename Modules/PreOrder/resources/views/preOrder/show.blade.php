@extends('layouts.app')
@section('header-title', __('Order Details'))

@section('content')

    <div class="row my-3">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between gap-2 py-3">
                    <h4 class="card-title mb-0">{{ __('Order Details') }}</h4>
                    <div class="d-flex gap-2 flex-wrap">
                        <a href="{{ route('shop.preOrder.paymentInvoice', $preOrder->id) }}" target="_blank"
                            class="btn btn-success py-2.5">
                            <img src="{{ asset('assets/icons-admin/download-alt.svg') }}" alt="icon" loading="lazy"
                                width="20" />
                            {{ __('Payment Slip') }}
                        </a>
                        <a href="{{ route('shop.preOrder.invoice', $preOrder->id) }}" target="_blank"
                            class="btn btn-primary py-2.5">
                            <img src="{{ asset('assets/icons-admin/download-alt.svg') }}" alt="icon" loading="lazy"
                                width="20" />
                            {{ __('Download Invoice') }}
                        </a>
                        @if (module_exists('Purchase'))
                            @hasPermission(['shop.preOrder.purchase.store'])
                                @if ($preOrder->purchase_id)
                                    <a href="{{ route('shop.purchase.show', $preOrder->purchase_id) }}"
                                        class="btn btn-outline-success py-2.5">
                                        {{ __('View Purchase') }}
                                    </a>
                                @elseif (! in_array($preOrder->order_status->value, ['Cancelled', 'Refunded', 'Delivered']))
                                    <button type="button" class="btn btn-outline-primary py-2.5" data-bs-toggle="modal"
                                        data-bs-target="#addToPurchaseModal">
                                        {{ __('Add to Purchase') }}
                                    </button>
                                @endif
                            @endhasPermission
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    <div class="d-flex gap-3 flex-wrap align-items-center">
                        <div class="flex-grow-1">
                            <div class="order-item">
                                <label class="label">{{ __('Order Id') }}:</label>
                                <span class="value">#{{ $preOrder->order_code }}</span>
                            </div>
                            <div class="order-item">
                                <label class="label">{{ __('Payment Status') }}:</label>
                                <span class="value">{{ $preOrder->payment_status }}</span>
                            </div>
                            <div class="order-item">
                                <label class="label">{{ __('Payment Method') }}:</label>
                                <span class="value">{{ $preOrder->payment_method }}</span>
                            </div>
                        </div>

                        <div class="item-divider"></div>

                        <div class="flex-grow-1">
                            <div class="order-item">
                                <label class="label">{{ __('Order Status') }}:</label>
                                @php
                                    $statusClasses = [
                                        'Cancelled' => 'badge badge-danger',
                                        'Delivered' => 'badge badge-success',
                                    ];

                                    $class = $statusClasses[$preOrder->order_status->value] ?? 'badge badge-warning';
                                @endphp
                                <span class="{{ $class }}">{{ $preOrder->order_status->label() }}</span>
                            </div>
                            <div class="order-item">
                                <label class="label">{{ __('Order Date') }}:</label>
                                <span class="value">{{ $preOrder->created_at->format('M d, Y') }}</span>
                            </div>
                            <div class="order-item">
                                <label class="label">{{ __('Delivery Date') }}:</label>
                                <span
                                    class="value">{{ $preOrder->delivery_date ? Carbon\Carbon::parse($preOrder->delivery_date)->format('M d, Y') : '-' }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive mt-4 mb-0">
                        <table class="table border-left-right">
                            <thead>
                                <tr>
                                    <th>{{ __('Product') }}</th>
                                    @if ($businessModel == 'multi')
                                        <th>{{ __('Shop') }}</th>
                                    @endif
                                    <th>{{ __('Quantity') }}</th>
                                    <th>{{ __('Unit') }}</th>
                                    <th>{{ __('Product Price') }}</th>
                                    <th>{{ __('Discount') }}</th>
                                    <th>{{ __('Price') }}</th>
                                    <th class="text-end">{{ __('Total') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $item = $preOrder->preOrderItem;
                                @endphp
                                <tr>
                                    <td>
                                        <div class="d-flex gap-1 align-items-center">
                                            <img src="{{ $item?->product?->thumbnail }}" alt="product" width="40"
                                                height="40" loading="lazy">
                                            <span>{{ $item->product_name }}</span>
                                        </div>
                                    </td>
                                    @if ($businessModel == 'multi')
                                        <td>{{ $item->product?->shop?->name }}</td>
                                    @endif
                                    <td>{{ $item->quantity }}</td>
                                    <td>{{ $item->unit }}</td>
                                    <td>{{ showCurrency($item->product_price) }}</td>
                                    <td>{{ showCurrency($item->discount) }}</td>
                                    <td>{{ showCurrency($item->price) }}</td>
                                    <td class="text-end">{{ showCurrency($item->quantity * $item->price) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="max-300 ms-auto d-flex flex-column gap-1">
                        <div class="d-flex align-items-center justify-content-between gap-2">
                            <div>{{ __('Sub Total') }}</div>
                            <div>{{ showCurrency($preOrder->total_amount) }}</div>
                        </div>
                        <div class="d-flex align-items-center justify-content-between gap-2">
                            <div>{{ __('Delivery Charge') }}</div>
                            <div>{{ showCurrency($preOrder->delivery_charge) }}</div>
                        </div>
                        <div class="d-flex align-items-center justify-content-between gap-2 border-top pt-1 mt-1">
                            <div class="fw-bold">{{ __('Grand Total') }}</div>
                            <div class="fw-bold">{{ showCurrency($preOrder->payable_amount) }}</div>
                        </div>
                        <div class="d-flex align-items-center justify-content-between gap-2 border-top pt-1 mt-1">
                            <div >{{ __('Paid Amount') }}</div>
                            <div class="text-success">    {{ $preOrder->isPaid() ? showCurrency($preOrder->payable_amount) : showCurrency($preOrder->paid_amount) }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!--##### Customer Info #####-->
            <div class="mt-3 card">
                <h5 class="fz-16 border-bottom px-3 py-12 m-0">{{ __('Customer Info') }}</h5>
                <div class="border-bottom px-3 py-2 d-flex  align-items-center gap-3">
                    <span class="text-color">{{ __('Name') }}: </span>
                    <span class="fw-medium">{{ $preOrder->customer?->user?->name }}</span>
                </div>
                <div class="px-3 py-2 d-flex  align-items-center gap-3">
                    <span class="text-color">{{ __('Phone') }}: </span>
                    <span class="fw-medium">{{ $preOrder->customer?->user?->phone }}</span>
                </div>
                <div class="px-3 py-2 d-flex  align-items-center gap-3">
                    <span class="text-color">{{ __('Email') }}: </span>
                    <span class="fw-medium">{{ $preOrder->customer?->user?->email }}</span>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!--##### Order & Shipping Info #####-->
            <div class="card">
                <h5 class="fz-18 border-bottom p-3 m-0">{{ __('Order & Shipping Info') }}</h5>
                <div class="px-3 py-2 d-flex justify-content-between align-items-center flex-wrap gap-2 border-bottom">
                    <div class="text-color">{{ __('Change Order Status') }}</div>
                    <div class="dropdown">
                        <a class="btn border text-start dropdown-toggle" href="#" role="button"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            {{ $preOrder->order_status->label() }}
                        </a>
                            @hasPermission(['shop.preOrder.status.change'])
                                <ul class="dropdown-menu order-status">
                                    @foreach ($orderStatus as $status)
                                        @php
                                            $needsConfirm = in_array($status->value, ['Cancelled', 'Refunded']);
                                        @endphp
                                        <li>
                                            <a class="dropdown-item"
                                                href="{{ route('shop.preOrder.status.change', $preOrder->id) }}?status={{ $status->value }}"
                                                @if ($needsConfirm) data-bs-toggle="modal" data-bs-target="#confirmActionModal"
                                                    data-url="{{ route('shop.preOrder.status.change', $preOrder->id) }}?status={{ $status->value }}"
                                                    data-message="{{ __('Are you sure you want to :action this order?', ['action' => strtolower($status->label())]) }}" @endif>
                                                {{ __($status->label()) }}
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            @endhasPermission
                    </div>
                </div>
                <div class="border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2 p-3">
                    <div class="text-color">{{ __('Payment Status') }}</div>
                    <div class="d-flex align-items-center gap-1">
                        <span>{{ $preOrder->payment_status }}</span>
                        @hasPermission('shop.preOrder.payment.status')
                            <label class="switch mb-0">
                                <a href="{{ route('shop.preOrder.payment.status', $preOrder->id) }}">
                                    <input type="checkbox" {{ $preOrder->isPaid() ? 'checked' : '' }}>
                                    <span class="slider round"></span>
                                </a>
                            </label>
                        @endhasPermission
                    </div>
                </div>
                @hasPermission('shop.preOrder.assign.order')
                    @if (in_array($preOrder->order_status->value, ['Requested', 'Accepted']) && ! $courierTracking)
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 p-3">
                            <div class="fw-medium text-color">{{ __('Assign Rider') }}</div>
                            <div class="d-flex align-items-center gap-1">
                                @if ($preOrder->driverPreOrder)
                                    <span>{{ $preOrder->driverPreOrder->driver?->user?->fullName }}</span>
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
                    @hasPermission('shop.preOrder.assign.courier')
                        @if (! in_array($preOrder->order_status->value, ['Requested', 'Cancelled', 'Delivered', 'Refunded']) && ! $preOrder->driverPreOrder)
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 p-3">
                                <div class="fw-medium text-color">{{ __('Courier') }}</div>
                                <div class="d-flex align-items-center gap-1">
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
            <!--##### Refund #####-->
            @if ($preOrder->refund_status)
                @php
                    $refundValue = $preOrder->refund_status->value;
                    $refundBadge = [
                        'Requested' => 'badge badge-warning',
                        'Approved' => 'badge badge-info',
                        'Rejected' => 'badge badge-danger',
                        'Refunded' => 'badge badge-success',
                    ][$refundValue] ?? 'badge badge-secondary';
                @endphp
                <div class="card mt-3">
                    <h5 class="fz-18 border-bottom p-3 m-0 d-flex align-items-center justify-content-between gap-2">
                        <span>{{ __('Refund') }}</span>
                        <span class="{{ $refundBadge }}">{{ __($preOrder->refund_status->label()) }}</span>
                    </h5>
                    @if ($preOrder->refund_reason)
                        <div class="border-bottom px-3 py-2">
                            <div class="text-color mb-1">{{ __('Customer Reason') }}</div>
                            <div class="fw-medium">{{ $preOrder->refund_reason }}</div>
                        </div>
                    @endif
                    @if ($refundValue === 'Rejected' && $preOrder->refund_note)
                        <div class="border-bottom px-3 py-2">
                            <div class="text-color mb-1">{{ __('Rejection Note') }}</div>
                            <div class="fw-medium">{{ $preOrder->refund_note }}</div>
                        </div>
                    @endif
                    @if ($refundValue === 'Refunded')
                        <div class="border-bottom d-flex align-items-center justify-content-between gap-2 px-3 py-2">
                            <span class="text-color">{{ __('Refunded Amount') }}</span>
                            <span class="fw-medium text-success">{{ showCurrency($preOrder->refund_amount) }}</span>
                        </div>
                        @if ($preOrder->refunded_at)
                            <div class="d-flex align-items-center justify-content-between gap-2 px-3 py-2">
                                <span class="text-color">{{ __('Refunded On') }}</span>
                                <span class="fw-medium">{{ Carbon\Carbon::parse($preOrder->refunded_at)->format('M d, Y h:i A') }}</span>
                            </div>
                        @endif
                    @endif
                    @if ($refundValue === 'Requested')
                        @hasPermission(['shop.preOrder.refund.approve'])
                            <div class="d-flex gap-2 flex-wrap p-3">
                                <a href="{{ route('shop.preOrder.refund.approve', $preOrder->id) }}"
                                    class="btn btn-primary flex-grow-1">{{ __('Approve') }}</a>
                                <button type="button" class="btn btn-outline-danger flex-grow-1" data-bs-toggle="modal"
                                    data-bs-target="#rejectRefund">{{ __('Reject') }}</button>
                            </div>
                        @endhasPermission
                    @elseif ($refundValue === 'Approved')
                        @hasPermission(['shop.preOrder.refund.pay'])
                            <div class="p-3">
                                <a href="{{ route('shop.preOrder.refund.pay', $preOrder->id) }}"
                                    class="btn btn-success w-100" data-bs-toggle="modal"
                                    data-bs-target="#confirmActionModal"
                                    data-url="{{ route('shop.preOrder.refund.pay', $preOrder->id) }}"
                                    data-message="{{ __('Are you sure you want to pay this refund of :amount to the customer?', ['amount' => showCurrency($preOrder->paidTotal())]) }}">{{ __('Pay Refund') }}
                                    ({{ showCurrency($preOrder->paidTotal()) }})</a>
                            </div>
                        @endhasPermission
                    @endif
                </div>
            @endif
            <!--##### Shipping Address #####-->
            <div class="card mt-3">
                <h5 class="fz-18 border-bottom p-3 m-0">{{ __('Shipping Address') }}</h5>
                <div class="border-bottom d-flex align-items-center justify-content-between gap-2 px-3 py-12">
                    <span class="text-color">{{ __('Name') }}: </span>
                    <span class="fw-medium">{{ $preOrder->address?->name }}</span>
                </div>
                <div class="border-bottom d-flex align-items-center justify-content-between gap-2 px-3 py-12">
                    <span class="text-color">{{ __('Phone') }}: </span>
                    <span class="fw-medium">{{ $preOrder->address?->phone }}</span>
                </div>
                <div class="border-bottom d-flex align-items-center justify-content-between gap-2 px-3 py-12">
                    <span class="text-color">{{ __('Address Type') }}: </span>
                    <span class="fw-medium">{{ $preOrder->address?->address_type }}</span>
                </div>
                <div class="border-bottom d-flex align-items-center justify-content-between gap-2 px-3 py-12">
                    <span class="text-color">{{ __('Thana') }}: </span>
                    <span class="fw-medium">{{ $preOrder->address?->thana?->name }}</span>
                </div>
                <div class="border-bottom d-flex align-items-center justify-content-between gap-2 px-3 py-12">
                    <span class="text-color">{{ __('Area') }}: </span>
                    <span class="fw-medium">{{ $preOrder->address?->area }}</span>
                </div>
                <div class="d-flex gap-2 border-bottom align-items-center justify-content-between flex-wrap px-3 py-12">
                    <div>
                        <span class="text-color">{{ __('Road No') }}: </span>
                        <span class="fw-medium">{{ $preOrder->address?->road_no }}</span>,
                    </div>
                    <div>
                        <span class="text-color">{{ __('Flat No') }}: </span>
                        <span class="fw-medium">{{ $preOrder->address?->flat_no }}</span>,
                    </div>
                    <div>
                        <span class="text-color">{{ __('House No') }}: </span>
                        <span class="fw-medium">{{ $preOrder->address?->house_no }}</span>
                    </div>
                </div>
                <div class="border-bottom d-flex align-items-center justify-content-between gap-2 px-3 py-12">
                    <span class="text-color">{{ __('Post Code') }}: </span>
                    <span class="fw-medium">{{ $preOrder->address?->post_code }}</span>
                </div>
                <div class="border-bottom d-flex align-items-center justify-content-between gap-2 px-3 py-12">
                    <span class="text-color">{{ __('Address Line') }}: </span>
                    <span class="fw-medium">{{ $preOrder->address?->address_line }}</span>
                </div>
                <div class="d-flex align-items-center justify-content-between gap-2 px-3 py-12">
                    <span class="text-color">{{ __('Address Line') }} 2: </span>
                    <span class="fw-medium">{{ $preOrder->address?->address_line2 }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Reject Refund Modal -->
    @if ($preOrder->refund_status && $preOrder->refund_status->value === 'Requested')
        <form action="{{ route('shop.preOrder.refund.reject', $preOrder->id) }}" method="POST">
            @csrf
            <div class="modal fade" id="rejectRefund">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h3 class="modal-title fs-5">{{ __('Reject Refund Request') }}</h3>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <label class="form-label">{{ __('Rejection Note') }}</label>
                            <textarea name="refund_note" rows="3" class="form-control"
                                placeholder="{{ __('Reason for rejecting this refund (optional)') }}"></textarea>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Close') }}</button>
                            <button type="submit" class="btn btn-danger">{{ __('Reject Refund') }}</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    @endif

    <!-- Confirm Action Modal -->
    <div class="modal fade" id="confirmActionModal">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title fs-5">{{ __('Are you sure?') }}</h3>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p id="confirmActionMessage" class="mb-0">{{ __('Do you want to proceed with this action?') }}</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('No') }}</button>
                    <a href="#" id="confirmActionYes" class="btn btn-primary">{{ __('Yes') }}</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Add to Purchase Modal -->
    @if (module_exists('Purchase') && ! $preOrder->purchase_id)
        @php $purchaseItem = $preOrder->preOrderItem; @endphp
        <form action="{{ route('shop.preOrder.purchase.store', $preOrder->id) }}" method="POST">
            @csrf
            <div class="modal fade" id="addToPurchaseModal">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h3 class="modal-title fs-5">{{ __('Add to Purchase') }}</h3>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">{{ __('Product') }}</label>
                                <input type="text" class="form-control"
                                    value="{{ $purchaseItem?->product_name }} ({{ __('Qty') }}: {{ $purchaseItem?->quantity }})"
                                    readonly>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">{{ __('Supplier') }} <span class="text-danger">*</span></label>
                                @if (count($suppliers) > 0)
                                    <select name="supplier_id" class="form-control" required>
                                        <option value="">{{ __('Select supplier') }}</option>
                                        @foreach ($suppliers as $supplier)
                                            <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                        @endforeach
                                    </select>
                                @else
                                    <div class="text-danger">
                                        {{ __('No supplier found. Please create a supplier first.') }}
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary"
                                data-bs-dismiss="modal">{{ __('Close') }}</button>
                            <button type="submit" class="btn btn-primary"
                                {{ count($suppliers) > 0 ? '' : 'disabled' }}>{{ __('Create Purchase') }}</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    @endif

    <!-- Assign Rider Modal -->
    <form action="{{ route('shop.preOrder.assign.order', $preOrder->id) }}" method="POST">
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
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Close') }}</button>
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
        <form action="{{ route('shop.preOrder.assign.courier', $preOrder->id) }}" method="POST">
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
                                {{ __('This pre-order will be booked with SteadFast courier for delivery. Please review before confirming.') }}
                            </p>
                            <div class="steadfast-summary">
                                <div class="steadfast-summary-row">
                                    <span class="text-color">{{ __('Recipient') }}</span>
                                    <span class="fw-medium">{{ $preOrder->address?->name }}</span>
                                </div>
                                <div class="steadfast-summary-row">
                                    <span class="text-color">{{ __('Phone') }}</span>
                                    <span class="fw-medium">{{ $preOrder->address?->phone }}</span>
                                </div>
                                <div class="steadfast-summary-row">
                                    <span class="text-color">{{ __('Address') }}</span>
                                    <span class="fw-medium text-end">{{ $preOrder->address?->address_line }}{{ $preOrder->address?->thana?->name ? ', '.$preOrder->address->thana->name : '' }}{{ $preOrder->address?->area ? ', '.$preOrder->address->area : '' }}</span>
                                </div>
                                <div class="steadfast-summary-row">
                                    <span class="text-color">{{ __('COD Amount') }}</span>
                                    <span class="fw-medium">{{ number_format($preOrder->payable_amount ?? $preOrder->total_amount, 2) }}</span>
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
@endsection
@push('css')
    <style>
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

        @media (max-width: 768px) {
            .item-divider {
                display: none;
            }
        }
    </style>
@endpush
@push('scripts')
    <script>
        (function () {
            var confirmModal = document.getElementById('confirmActionModal');
            if (!confirmModal) return;
            confirmModal.addEventListener('show.bs.modal', function (event) {
                var trigger = event.relatedTarget;
                if (!trigger) return;
                var url = trigger.getAttribute('data-url');
                var message = trigger.getAttribute('data-message');
                var yesBtn = document.getElementById('confirmActionYes');
                var messageEl = document.getElementById('confirmActionMessage');
                if (url && yesBtn) yesBtn.setAttribute('href', url);
                if (message && messageEl) messageEl.textContent = message;
            });
        })();
    </script>
@endpush
