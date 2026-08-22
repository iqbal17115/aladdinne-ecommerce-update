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
                        <thead class="table-dark">
                            <tr class="text-center">
                                <th class="text-center">{{ __('SL') }}</th>
                                <th class="text-start">{{ __('Product Name') }}</th>
                                <th>{{ __('Current Stock') }}</th>
                                <th>{{ __('Cart Quantity') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($addToCart as $product)
                                <tr class="text-center">
                                    <td class="text-center">{{ $loop->iteration }}</td>
                                    <td class="text-start">{{ $product->product_name ?? '--' }}</td>
                                    <td>{{ $product->total_stock ?? '--' }}</td>
                                    <td>{{ $product->total_quantity ?? '--' }}</td>
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
            {{ $addToCart->withQueryString()->links() }}
        </div>
    </div>
@endsection
@push('scripts')
    <script src="{{ asset('modules/report/js/report.js') }}"></script>
@endpush
