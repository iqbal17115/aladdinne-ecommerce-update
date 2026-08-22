@extends('layouts.app')
@section('content')
    @push('css')
        <link rel="stylesheet" href="{{ asset('modules/report/css/report.css') }}">
    @endpush
    <div class="container-fluid mt-3">
        <div class="mb-3 card">
            <div class="card-body">
                <div class="widget_container col-span-6 rounded-3 grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 p-3 mb-3">
                    <div class="glassmorphism box-1">
                        <p class="fs-4 fw-semibold mb-3 price">{{ showCurrency(formatAmount($totalAdminCommission)) }}</p>
                        <div class="d-flex justify-content-between">
                            <p class="fs-6 fw-normal mb-0">{{ __('Admin Commission') }}</p>
                            <div class="glassmorphismIconBox">
                                <img src="{{ asset('assets/reportImage/payment.svg') }}" alt="">
                            </div>
                        </div>
                    </div>
                    <div class="glassmorphism box-2">
                        <p class="fs-4 fw-semibold mb-3 price">{{ showCurrency(formatAmount($totalRiderCost)) }}</p>
                        <div class="d-flex justify-content-between">
                            <p class="fs-6 fw-normal mb-0">{{ __('Rider Cost') }}</p>

                            <div class="glassmorphismIconBox">
                                <img src="{{ asset('assets/reportImage/payment.svg') }}" alt="">
                            </div>
                        </div>
                    </div>
                    <div class="glassmorphism box-3">
                        <p class="fs-4 fw-semibold mb-3 price">{{ showCurrency(formatAmount($totalRefundAmount)) }}</p>
                        <div class="d-flex justify-content-between">
                            <p class="fs-6 fw-normal mb-0">{{ __('Refund') }}</p>

                            <div class="glassmorphismIconBox">
                                <img src="{{ asset('assets/reportImage/payment.svg') }}" alt="">
                            </div>
                        </div>
                    </div>
                    <div class="glassmorphism box-4">
                        <p class="fs-4 fw-semibold mb-3 price">{{ showCurrency(formatAmount($totalWithdrawal)) }}</p>
                        <div class="d-flex justify-content-between">
                            <p class="fs-6 fw-normal mb-0">{{ __('Withdrawal') }}</p>

                            <div class="glassmorphismIconBox">
                                <img src="{{ asset('assets/reportImage/payment.svg') }}" alt="">
                            </div>
                        </div>
                    </div>
                    <div class="glassmorphism box-5">
                        <p class="fs-4 fw-semibold mb-3 price">{{ showCurrency(formatAmount($totalMaintenanceFee)) }}</p>
                        <div class="d-flex justify-content-between">
                            <p class="fs-6 fw-normal mb-0">{{ __('Maintenance Cost') }}</p>

                            <div class="glassmorphismIconBox">
                                <img src="{{ asset('assets/reportImage/payment.svg') }}" alt="">
                            </div>
                        </div>
                    </div>
                </div>
                @include('report::components.earningFilter')
                <div class="table-responsive" id="printSection">
                    <table class="table table-bordered table-responsive-lg">
                        <thead>
                            <tr class="text-center">
                                <th class="text-start">{{ __('PayOut Reason') }}</th>
                                <th>{{ __('Amount') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="text-center">
                                <td class="text-start">{{ __('Rider Cost') }}</td>
                                <td>{{ showCurrency(formatAmount($orders->riderCost)) }}</td>
                            </tr>
                            <tr class="text-center">
                                <td class="text-start">{{ __('Admin Commission') }}</td>
                                <td>{{ showCurrency(formatAmount($orders->adminCommission)) }}</td>
                            </tr>
                            <tr class="text-center">
                                <td class="text-start">{{ __('Refund') }}</td>
                                <td>{{ showCurrency(formatAmount($refundAmount)) }}</td>
                            </tr>
                            <tr class="text-center">
                                <td class="text-start">{{ __('Maintenance Fee') }}</td>
                                <td>{{ showCurrency(formatAmount($maintenanceFee)) }}</td>
                            </tr>
                            <tr class="text-center">
                                <td class="text-start">{{ __('Withdrawal') }}</td>
                                <td>{{ showCurrency(formatAmount($withdrawal)) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script src="{{ asset('modules/report/js/report.js') }}"></script>
@endpush
