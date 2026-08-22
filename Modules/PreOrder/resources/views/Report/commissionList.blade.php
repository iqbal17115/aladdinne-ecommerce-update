@extends('layouts.app')
@section('content')
    @push('css')
        <link rel="stylesheet" href="{{ asset('modules/PreOrder/css/style.css') }}">
    @endpush
    <div class="container-fluid mt-3">
        <div class="mb-3 card">
            <div class="card-body">
                <div class="widget_container col-span-6 rounded-3 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 p-3 mb-3">
                    <div class="glassmorphism box-1">
                        <p class="fs-4 fw-semibold mb-3 price">
                            {{ showCurrency(formatAmount($totalOrder->totalOrderAmount)) }}</p>
                        <div class="d-flex justify-content-between">
                            <p class="fs-6 fw-normal mb-0">{{ __('Total Order') }}</p>
                            <div class="glassmorphismIconBox">
                                <img src="{{ asset('assets/reportImage/stock.svg') }}" alt="">
                            </div>
                        </div>
                    </div>
                    <div class="glassmorphism box-2">
                        <p class="fs-4 fw-semibold mb-3 price">{{ showCurrency(formatAmount($totalOrder->totalEarning)) }}
                        </p>
                        <div class="d-flex justify-content-between">
                            <p class="fs-6 fw-normal mb-0">{{ __('Selling Earning') }}</p>
                            <div class="glassmorphismIconBox">
                                <img src="{{ asset('assets/reportImage/wallet.svg') }}" alt="" style="">
                            </div>
                        </div>
                    </div>
                    <div class="glassmorphism box-3">
                        <p class="fs-4 fw-semibold mb-3 price">
                            {{ showCurrency(formatAmount($totalOrder->totalAdminCommission)) }}</p>
                        <div class="d-flex justify-content-between">
                            <p class="fs-6 fw-normal mb-0">{{ __('Admin Commission') }}</p>
                            <div class="glassmorphismIconBox">
                                <img src="{{ asset('assets/reportImage/payment.svg') }}" alt="">
                            </div>
                        </div>
                    </div>
                </div>
                @include('preorder::components.filterSection')
                <div class="table-responsive" id="printSection">
                    <table class="table table-bordered table-responsive-lg">
                        <thead>
                            <tr class="text-center">
                                <th class="text-center">{{ __('SL') }}.</th>
                                <th class="text-start">{{ __('Order ID') }}</th>
                                <th>{{ __('Created Date') }}</th>
                                <th>{{ __('Total Product') }}</th>
                                <th>{{ __('Admin Commission') }}</th>
                                <th>{{ __('Selling Earning') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($preOrders as $order)
                                <tr class="text-center">
                                    <td class="text-center">{{ $loop->iteration }}</td>
                                    <td class="text-start">{{ $order->prefix . $order->order_code ?? '--' }}</td>
                                    <td>{{ $order->created_at->format('d F, Y') ?? '--' }}</td>
                                    <td>{{ $order->total_product }}</td>
                                    <td>{{ showCurrency(formatAmount($order->admin_commission)) }}</td>
                                    <td>{{ showCurrency(formatAmount($order->sale_earning)) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td class="text-center" colspan="100%">{{ __('No Data Found') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="my-3">
                    {{ $preOrders->withQueryString()->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script src="{{ asset('modules/PreOrder/js/app.js') }}"></script>
@endpush
