@extends('layouts.app')

@section('header-title', __('Orders'))

@section('content')
    <div class="admin-order-index">
        <div class="card">
            <div class="card-body">
                <ul class="nav nav-tabs order-tabs">
                @php
                    use App\Enums\OrderStatus;
                    $orderStatuses = OrderStatus::cases();
                @endphp


                        <li class="nav-item">
                        <a href="{{ route('admin.order.index') }}"
                            class="nav-link {{ request()->url() === route('admin.order.index') ? 'active' : '' }}">
                            {{ __('All') }}
                            {{-- <span class="count statusAll">{{ $allOrders > 99 ? '99+' : $allOrders }}</span> --}}
                        </a>
                        </li>

                        @foreach ($orderStatuses as $status)

                        <li class="nav-item">
                            <a href="{{ route('admin.order.index', str_replace(' ', '_', $status->value)) }}"
                                class="nav-link {{ request()->url() === route('admin.order.index', str_replace(' ', '_', $status->value)) ? 'active' : '' }}">
                                <span>{{ __($status->value) }}</span>
                            </a>
                        </li>
                        @endforeach

                </ul>
                <div class="table-responsive">

                    <table class="table border-left-right table-responsive-lg order-index-table">
                        <thead>
                            <tr>
                                <th style="min-width: 85px">{{ __('Order ID') }}</th>
                                <th>{{ __('Order Date') }}</th>
                                <th>{{ __('Customer') }}</th>
                                @if ($businessModel == 'multi')
                                    <th>{{ __('Shop') }}</th>
                                @endif
                                <th>{{ __('Total Amount') }}</th>
                                <th>{{ __('Payment Method') }}</th>
                                <th>{{ __('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($orders as $order)
                                <tr>
                                    <td class="w-auto order-code-cell">{{ $order->prefix . $order->order_code }}</td>
                                    <td class="w-min order-date-cell">{{ $order->created_at->format('d M Y, h:i A') }}</td>
                                    <td class="w-min order-customer-cell">{{ $order->customer?->user?->name }}</td>

                                    @if ($businessModel == 'multi')
                                        <td class="w-min order-shop-cell">
                                            {{ $order->shop?->name }}
                                        </td>
                                    @endif
                                    <td class="w-min order-amount-cell">
                                        {{ showCurrency($order->payable_amount) }}
                                        <br>
                                        <span class="badge rounded-pill text-bg-primary order-payment-badge">{{ $order->payment_status }}</span>
                                    </td>
                                    <td class="w-min order-method-cell">{{ $order->payment_method }}</td>
                                    <td class="w-min order-action-cell">
                                        @hasPermission('admin.order.show')
                                            <a href="{{ route('admin.order.show', $order->id) }}" data-bs-toggle="tooltip"
                                                data-bs-placement="top" data-bs-title="{{ __('view details') }}"
                                                class="circleIcon svg-bg">
                                                <img src="{{ asset('assets/icons-admin/eye.svg') }}" alt="icon"
                                                    loading="lazy" />
                                            </a>
                                        @endhasPermission
                                        <a href="{{ route('shop.download-invoice', $order->id) }}" data-bs-toggle="tooltip"
                                            data-bs-placement="left" data-bs-title="{{ __('Download Invoice') }}"
                                            class="circleIcon btn-outline-secondary">
                                            <img src="{{ asset('assets/icons-admin/download-alt.svg') }}" alt="icon"
                                                loading="lazy" />
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="100%" class="text-center">
                                        {{ __('No order found') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                </div>

            </div>
        </div>

        <div class="my-3 order-pagination">
            {{ $orders->links() }}
        </div>
    </div>

@endsection

@push('css')
    <style>
        .admin-order-index .order-tabs {
            gap: 10px;
            margin-bottom: 1rem;
            border-bottom: 0;
            flex-wrap: wrap;
        }

        .admin-order-index .order-tabs .nav-item {
            flex: 0 0 auto;
        }

        .admin-order-index .order-tabs .nav-link {
            border: 1px solid #d7dae0;
            border-radius: 999px;
            padding: 0.55rem 0.9rem;
            white-space: nowrap;
        }

        .admin-order-index .order-index-table {
            min-width: 760px;
        }

        .admin-order-index .order-index-table td,
        .admin-order-index .order-index-table th {
            vertical-align: middle;
        }

        .admin-order-index .order-code-cell,
        .admin-order-index .order-date-cell,
        .admin-order-index .order-customer-cell,
        .admin-order-index .order-shop-cell,
        .admin-order-index .order-method-cell {
            white-space: nowrap;
        }

        .admin-order-index .order-amount-cell {
            min-width: 140px;
        }

        .admin-order-index .order-payment-badge {
            margin-top: 6px;
            display: inline-flex;
        }

        .admin-order-index .order-action-cell {
            white-space: nowrap;
        }

        .admin-order-index .order-action-cell .circleIcon,
        .admin-order-index .order-action-cell .btn-outline-secondary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .admin-order-index .order-pagination .pagination {
            flex-wrap: wrap;
            gap: 8px;
        }

        @media (max-width: 991.98px) {
            .admin-order-index .card-body {
                padding: 1rem;
            }

            .admin-order-index .order-tabs {
                gap: 8px;
            }

            .admin-order-index .order-index-table {
                min-width: 700px;
            }
        }

        @media (max-width: 767.98px) {
            .admin-order-index .order-tabs {
                flex-wrap: nowrap;
                overflow-x: auto;
                padding-bottom: 4px;
                scrollbar-width: thin;
            }

            .admin-order-index .order-tabs .nav-link {
                padding: 0.5rem 0.85rem;
                font-size: 14px;
            }

            .admin-order-index .order-index-table {
                min-width: 640px;
            }

            .admin-order-index .order-action-cell {
                display: flex;
                flex-wrap: wrap;
                gap: 8px;
            }

            .admin-order-index .order-payment-badge {
                white-space: normal;
            }
        }

        @media (max-width: 575.98px) {
            .admin-order-index .card-body {
                padding: 0.875rem;
            }

            .admin-order-index .order-tabs {
                margin-bottom: 0.875rem;
            }

            .admin-order-index .order-tabs .nav-link {
                font-size: 13px;
            }

            .admin-order-index .order-index-table {
                min-width: 560px;
            }

            .admin-order-index .order-amount-cell {
                min-width: 120px;
            }
        }
    </style>
@endpush
