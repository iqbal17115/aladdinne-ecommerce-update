@extends('layouts.app')
@section('content')
    @push('css')
        <link rel="stylesheet" href="{{ asset('modules/report/css/report.css') }}">
    @endpush
    <div class="container-fluid mt-3">
        <div class="mb-3 card">
            <div class="card-body">
                @include('preorder::components.orderFilter')
                <div class="table-responsive" id="printSection">
                    <table class="table table-bordered table-responsive-lg">
                        <thead class="table-dark">
                            <tr class="text-center">
                                <th class="text-center">{{ __('SL') }}</th>
                                @if($isRoot)<th>{{ __('Shop') }}</th>@endif
                                <th>{{ __('Customer') }}</th>
                                <th>{{ __('Price/Payment') }}</th>
                                <th>{{ __('Order Date') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th>{{ __('Payment Status') }}</th>
                                <th>{{ __('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($preOrders as $order)
                                <tr class="text-center">
                                    <td class="text-center">{{ $order->order_code }}</td>
                                     @if($isRoot)<td class="text-center">{{ $order->shop->name ?? '--' }}</td>@endif
                                    <td>{{ $order->customer->user->name ?? '--' }} <br> {{ $order->customer->user->phone ?? '--' }}</td>
                                    <td>{{ showCurrency($order->payable_amount) }}</td>
                                    <td>{{ $order->created_at->format('d M y') }}</td>
                                    <td>{{ $order->order_status->label() ?? '' }}</td>
                                    <td>{{ $order->payment_status }}</td>
                                    <td>
                                        <a href="{{ route('shop.preOrder.show', $order->id) }}" class="btn btn-info">
                                            <i class="fa fa-eye"></i>
                                        </a>
                                        <a href="{{ route('shop.preOrder.invoice', $order->id) }}" class="btn btn-info">
                                            <i class="fa fa-download"></i>
                                        </a>
                                    </td>
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
            {{ $preOrders->withQueryString()->links() }}
        </div>
    </div>
@endsection
@push('scripts')
    <script src="{{ asset('modules/report/js/report.js') }}"></script>
@endpush
