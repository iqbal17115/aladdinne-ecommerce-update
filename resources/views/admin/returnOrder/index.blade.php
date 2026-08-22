@extends('layouts.app')

@section('header-title', __('Orders'))

@section('content')
    <div class="admin-return-order-index">
        <div class="card">
            <div class="card-body">
                <div class="table-responsive return-order-table-wrap">

                    <table class="table border-left-right return-order-table w-100">
                        <thead>
                            <tr>
                                <th style="min-width: 85px">{{ __('Order ID') }}</th>
                                <th>{{ __('Return Date') }}</th>
                                <th>{{ __('Customer') }}</th>
                                @if ($businessModel == 'multi')
                                    <th>{{ __('Shop') }}</th>
                                @endif
                                <th>{{ __('Amount') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th>{{ __('Payment Status') }}</th>
                                <th>{{ __('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($returnOrder as $order)
                                <tr>
                                    <td class="w-auto return-order-code-cell">{{ $order->order->prefix . $order->order->order_code }}</td>
                                    <td class="w-min return-order-date-cell">{{ $order->created_at->format('d M Y, h:i A') }}</td>
                                    <td class="w-min return-order-customer-cell">{{ $order->customer?->user?->name }}</td>

                                    @if ($businessModel == 'multi')
                                        <td class="w-min return-order-shop-cell">
                                            {{ $order->shop?->name }}
                                        </td>
                                    @endif
                                    <td class="w-min return-order-amount-cell">
                                        {{ showCurrency($order->amount) }}
                                    </td>
                                    <td class="w-min return-order-status-cell">
                                        {{ $order->status }}
                                    </td>
                                    <td class="return-order-payment-cell">
                                        <button class="badge rounded-pill text-bg-{{ $order->payment_status ? 'success' : 'danger' }}">{{ $order->payment_status ? 'Paid' : 'Unpaid' }}</button>
                                    </td>
                                    <td class="w-min return-order-action-cell">
                                        @hasPermission('admin.returnOrder.show')
                                            <a href="{{ route('admin.returnOrder.show', $order->id) }}" data-bs-toggle="tooltip"
                                                data-bs-placement="top" data-bs-title="{{ __('view details') }}"
                                                class="circleIcon svg-bg">
                                                <img src="{{ asset('assets/icons-admin/eye.svg') }}" alt="icon"
                                                    loading="lazy" />
                                            </a>
                                        @endhasPermission
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

        <div class="my-3 return-order-pagination">
            {{ $returnOrder->links() }}
        </div>
    </div>

@endsection

@push('css')
    <style>
        .admin-return-order-index .return-order-table {
            width: max-content;
            min-width: 820px;
        }

        .admin-return-order-index .return-order-table-wrap {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .admin-return-order-index .return-order-table-wrap::-webkit-scrollbar {
            height: 8px;
        }

        .admin-return-order-index .return-order-table-wrap::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 999px;
        }

        .admin-return-order-index .return-order-table-wrap::-webkit-scrollbar-track {
            background: transparent;
        }

        .admin-return-order-index .return-order-table {
            min-width: 820px;
        }

        .admin-return-order-index .return-order-table td,
        .admin-return-order-index .return-order-table th {
            vertical-align: middle;
        }

        .admin-return-order-index .return-order-code-cell,
        .admin-return-order-index .return-order-date-cell,
        .admin-return-order-index .return-order-customer-cell,
        .admin-return-order-index .return-order-shop-cell,
        .admin-return-order-index .return-order-amount-cell,
        .admin-return-order-index .return-order-status-cell {
            white-space: nowrap;
        }

        .admin-return-order-index .return-order-payment-cell .badge {
            white-space: nowrap;
        }

        .admin-return-order-index .return-order-action-cell {
            white-space: nowrap;
        }

        .admin-return-order-index .return-order-action-cell .circleIcon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .admin-return-order-index .return-order-pagination .pagination {
            flex-wrap: wrap;
            gap: 8px;
        }

        
        @media (max-width: 991.98px) {
            .admin-return-order-index .card-body {
                padding: 1rem;
            }

            .admin-return-order-index .return-order-table {
                min-width: 760px;
            }
        }

        @media (max-width: 767.98px) {
            .admin-return-order-index .return-order-table {
                min-width: 680px;
            }

            .admin-return-order-index .return-order-payment-cell .badge {
                font-size: 12px;
            }
        }

        @media (max-width: 575.98px) {
            .admin-return-order-index .card-body {
                padding: 0.875rem;
            }

            .admin-return-order-index .return-order-table {
                min-width: 620px;
            }
        }
    </style>
@endpush
