@extends('layouts.app')

@section('header-title', __('Courier Tracking'))

@section('content')
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table border-left-right table-responsive-lg">
                    <thead>
                        <tr>
                            <th>{{ __('Order') }}</th>
                            <th>{{ __('Courier') }}</th>
                            <th>{{ __('Consignment ID') }}</th>
                            <th>{{ __('Tracking Code') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th>{{ __('Delivery Fee') }}</th>
                            <th>{{ __('Date') }}</th>
                            <th>{{ __('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($courierTrackings as $tracking)
                            <tr>
                                <td class="w-min">
                                    @if ($tracking->order)
                                        <a href="{{ route('admin.order.show', $tracking->order->id) }}">
                                            {{ $tracking->order->prefix }}{{ $tracking->order->order_code }}
                                        </a>
                                    @elseif ($tracking->preOrder)
                                        <a href="{{ route('shop.preOrder.show', $tracking->preOrder->id) }}">
                                            {{ $tracking->preOrder->order_code }}
                                        </a>
                                        <span class="badge rounded-pill text-bg-secondary">{{ __('Pre-order') }}</span>
                                    @else
                                        &mdash;
                                    @endif
                                </td>
                                <td class="w-min">{{ $tracking->courier_name }}</td>
                                <td class="w-min">{{ $tracking->consignment_id ?? '—' }}</td>
                                <td class="w-min">{{ $tracking->tracking_code ?? '—' }}</td>
                                <td class="w-min">
                                    <span class="badge rounded-pill text-bg-primary">{{ $tracking->status }}</span>
                                </td>
                                <td class="w-min">{{ number_format($tracking->delivery_fee, 2) }}</td>
                                <td class="w-min">{{ $tracking->created_at->format('d M Y, h:i A') }}</td>
                                <td class="w-min">
                                    @hasPermission('admin.courierTracking.refreshStatus')
                                        <form action="{{ route('admin.courierTracking.refreshStatus', $tracking->id) }}"
                                            method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-outline-primary btn-sm">
                                                {{ __('Refresh') }}
                                            </button>
                                        </form>
                                    @endhasPermission
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="100%" class="text-center">{{ __('No courier trackings found') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="my-3">
        {{ $courierTrackings->links() }}
    </div>
@endsection
