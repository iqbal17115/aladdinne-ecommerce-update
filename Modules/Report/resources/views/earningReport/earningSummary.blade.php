@extends('layouts.app')
@section('content')
    @push('css')
        <link rel="stylesheet" href="{{ asset('modules/report/css/report.css') }}">
    @endpush
    <div class="container-fluid mt-3">
        <div class="mb-3 card">
            <div class="card-body">
                @include('report::components.earningFilter')
                <div class="containerSummary py-5">
                    <div class="summary-card shadow-sm" id="printSection">
                        <!-- Header -->
                        <div class="text-center mb-4 pt-4 pb-4" style="background: #f6f7f9">
                            <img src="{{ $generaleSetting?->logo ?? asset('assets/logo.png') }}" width="100"
                                class="mb-2" alt="Logo">
                            <div class="summary-title">{{ __('Earning Summary') }}</div>
                            <div class="summary-date">{{ $from }} - {{ $to }}</div>
                        </div>
                        <!-- List -->
                        <div class="summary-row d-flex justify-content-between">
                            <span>{{ __('Total Sale') }}</span>
                            <span class="amount-positive">{{ showCurrency(formatAmount($totalSale)) }}</span>
                        </div>
                        <div class="summary-row d-flex justify-content-between">
                            <span>{{ __('Admin Commission') }}</span>
                            <span class="amount-negative">-{{ showCurrency(formatAmount($adminCommission)) }}</span>
                        </div>
                        <div class="summary-row d-flex justify-content-between">
                            <span>{{ __('Maintenance Cost') }}</span>
                            <span class="amount-negative">-{{ showCurrency(formatAmount($maintenanceFee)) }}</span>
                        </div>
                        <div class="summary-row d-flex justify-content-between">
                            <span>{{ __('Product Purchase') }}</span>
                            <span class="amount-negative">-{{ showCurrency(formatAmount($totalPurchase)) }}</span>
                        </div>
                        <div class="summary-row d-flex justify-content-between">
                            <span>{{ __('Delivery Cost') }}</span>
                            <span class="amount-negative">-{{ showCurrency(formatAmount($deliveryFee)) }}</span>
                        </div>
                        <div class="summary-row d-flex justify-content-between border-0">
                            <span>{{ __('Refund') }}</span>
                            <span class="amount-negative">-{{ showCurrency(formatAmount($totalRefundAmount)) }}</span>
                        </div>
                        <!-- Totals -->
                        <div class="total-box">
                            <div class="row total-profit">
                                <div class="col">{{ __('Total Profit') }}</div>
                                <div class="col text-end">{{ showCurrency(formatAmount($totalEarningProfit)) }}</div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script src="{{ asset('modules/report/js/report.js') }}"></script>
@endpush
