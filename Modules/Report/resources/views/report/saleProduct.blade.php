@extends('layouts.app')
@section('content')
    @push('css')
        <link rel="stylesheet" href="{{ asset('modules/report/css/report.css') }}">
    @endpush
    <div class="container-fluid mt-3">
        <div class="mb-3 card">
            <div class="card-body">
                @include('report::components.reportFilter')
                <div class="table-responsive" id="printSection">
                    <table class="table table-bordered table-responsive-lg">
                        <thead>
                            <tr class="text-center">
                                <th class="text-center">{{ __('SL') }}</th>
                                <th class="text-start">{{ __('Product Name') }}</th>
                                <th>{{ __('Total Sold Quantity') }}</th>
                                <th class="text-center">{{ __('Total Sales') }}</th>
                                <th class="text-center">{{ __('Net Amount') }}</th>
                                <th class="text-center">{{ __('Profit') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($orderProducts as $product)
                                <tr class="text-center">
                                    <td class="text-center">{{ $loop->iteration }}</td>
                                    <td class="text-start">{{ $product->product_name ?? '--' }}</td>
                                    <td>{{ $product->total_quantity ?? '--' }}</td>
                                    <td>{{ showCurrency(formatAmount($product->total_sale_amount)) }}</td>
                                    <td>{{ showCurrency(formatAmount($product->total_buying_amount)) }}</td>
                                    <td>{{ showCurrency(formatAmount($product->profit)) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td class="text-center" colspan="100%">{{ __('No Data Found') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="my-3">
            {{ $orderProducts->withQueryString()->links() }}
        </div>
    </div>
@endsection
@push('scripts')
    <script src="{{ asset('modules/report/js/report.js') }}"></script>
@endpush
