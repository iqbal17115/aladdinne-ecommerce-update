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
                            {{ showCurrency(formatAmount($totalReport->totalSale)) }}</p>
                        <div class="d-flex justify-content-between">
                            <p class="fs-6 fw-normal mb-0">{{ __('Total Sale') }}</p>
                            <div class="glassmorphismIconBox">
                                <img src="{{ asset('assets/reportImage/stock.svg') }}" alt="">
                            </div>
                        </div>
                    </div>
                    <div class="glassmorphism box-2">
                        <p class="fs-4 fw-semibold mb-3 price">
                            {{ showCurrency(formatAmount($totalReport->totalBuying)) }}</p>
                        <div class="d-flex justify-content-between">
                            <p class="fs-6 fw-normal mb-0">{{ __('Total Buying Cost') }}</p>
                            <div class="glassmorphismIconBox">
                                <img src="{{ asset('assets/reportImage/payment.svg') }}" alt="">
                            </div>
                        </div>
                    </div>
                    <div class="glassmorphism box-3">
                        <p class="fs-4 fw-semibold mb-3 price">
                            {{ showCurrency(formatAmount($totalReport->totalProfit)) }}</p>
                        <div class="d-flex justify-content-between">
                            <p class="fs-6 fw-normal mb-0">{{ __('Total Profit') }}</p>
                            <div class="glassmorphismIconBox">
                                <img src="{{ asset('assets/reportImage/wallet.svg') }}" alt="" style="">
                            </div>
                        </div>
                    </div>
                </div>
                <form action="">
                    <div class="row">
                        <div class="col-md-2 col-lg-2 col-12">
                            <h4>{{ __('Profit Report') }}</h4>
                        </div>
                        <div class="col-md-10 col-lg-10 col-12 ">
                            <div class="mb-3 d-flex align-items-center justify-content-end flex-wrap gap-2">
                                <div class="position-relative" style="max-width: 400px;">
                                    <i class="fa fa-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                                    <input type="text" name="search" class="form-control ps-5" placeholder="Search here"
                                        value="{{ request('search') }}">
                                </div>
                                <div class=" mx-2">
                                    <div class="date-range-wrapper d-flex align-items-center gap-2">
                                        <div class="date-input">
                                            <input type="text" id="datePicker" class="form-control datePickerfield"
                                                name="from" value="{{ $from }}" placeholder="mm/dd/yyyy" />
                                            <i class="bi bi-calendar3 dateIcon"></i>
                                        </div>
                                        <span class="date-separator">-</span>
                                        <div class="date-input">
                                            <input type="text" id="datePicker2" class="form-control datePickerfield"
                                                name="to" value="{{ $to }}" placeholder="mm/dd/yyyy" />
                                            <i class="bi bi-calendar3 dateIcon"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex gap-2">
                                    <button class="btn btn-primary" type="submit"><i class="fa fa-filter"></i>
                                        {{ __('Filter') }}</button>
                                    <a href="{{ route('shop.preOrder.profitReport') }}" class="btn btn-dark"><i
                                            class="fa fa-refresh"></i></a>
                                    <a href="{{ route('shop.preOrder.profitReportExport', request()->all()) }}"
                                        class="btn btn-warning">{{ __('Export') }} <i class="fa fa-download"></i></a>
                                    <a id="printBtn" onclick="printReport()" href="javascript:void(0)"
                                        class="btn btn-info"><i class="fa fa-print"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
                <div class="table-responsive" id="printSection">
                    <table class="table table-bordered table-responsive-lg">
                        <thead>
                            <tr class="text-center">
                                <th class="text-center">{{ __('SL') }}.</th>
                                <th class="text-start">{{ __('Order ID') }}</th>
                                <th>{{ __('Created Date') }}</th>
                                <th>{{ __('Total Product') }}</th>
                                <th>{{ __('Sale Amount') }}</th>
                                <th>{{ __('Buying Cost') }}</th>
                                <th>{{ __('Profit') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($preOrders as $order)
                                <tr class="text-center">
                                    <td class="text-center">{{ $loop->iteration }}</td>
                                    <td class="text-start">{{ $order->order_code ?? '--' }}</td>
                                    <td>{{ $order->created_at->format('d F, Y') ?? '--' }}</td>
                                    <td>{{ $order->total_product }}</td>
                                    <td>{{ showCurrency(($order->sale_amount)) }}</td>
                                    <td>{{ showCurrency(($order->buying_amount)) }}</td>
                                    <td>{{ showCurrency(($order->profit)) }}</td>
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
