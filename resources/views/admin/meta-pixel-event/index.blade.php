@extends('layouts.app')

@section('header-title', __('Meta Pixel Events'))

@section('content')
    <div class="admin-meta-pixel-event-index">
        <div class="card">
            <div class="card-body">
                <form method="GET" action="{{ route('admin.metaPixelEvent.index') }}" class="row gy-3 align-items-end mb-4">
                    <div class="col-lg-3 col-md-6">
                        <label class="form-label">{{ __('Event Type') }}</label>
                        <select name="event_name" class="form-select">
                            <option value="">{{ __('All Events') }}</option>
                            @foreach ($eventNames as $name)
                                <option value="{{ $name }}" {{ $eventName == $name ? 'selected' : '' }}>{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>

                    @if ($shops->count())
                        <div class="col-lg-3 col-md-6">
                            <label class="form-label">{{ __('Shop') }}</label>
                            <select name="shop_id" class="form-select">
                                <option value="">{{ __('All Shops') }}</option>
                                @foreach ($shops as $shop)
                                    <option value="{{ $shop->id }}" {{ (string) $shopId === (string) $shop->id ? 'selected' : '' }}>
                                        {{ $shop->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <div class="col-lg-2 col-md-6">
                        <label class="form-label">{{ __('From') }}</label>
                        <input type="date" name="from" class="form-control" value="{{ $from }}">
                    </div>

                    <div class="col-lg-2 col-md-6">
                        <label class="form-label">{{ __('To') }}</label>
                        <input type="date" name="to" class="form-control" value="{{ $to }}">
                    </div>

                    <div class="col-lg-2 col-md-6 d-flex gap-2">
                        <button type="submit" class="btn btn-primary">{{ __('Filter') }}</button>
                        <a href="{{ route('admin.metaPixelEvent.index') }}" class="btn btn-outline-secondary">{{ __('Reset') }}</a>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table border-left-right table-responsive-lg">
                        <thead>
                            <tr>
                                <th>{{ __('Date') }}</th>
                                <th>{{ __('Event') }}</th>
                                <th>{{ __('Page') }}</th>
                                <th>{{ __('Customer') }}</th>
                                <th>{{ __('Product') }}</th>
                                <th>{{ __('Shop') }}</th>
                                <th>{{ __('Value') }}</th>
                                <th>{{ __('IP / Device') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($events as $event)
                                <tr>
                                    <td class="w-min">{{ $event->created_at->format('d M Y, h:i A') }}</td>
                                    <td class="w-min">
                                        <span class="badge rounded-pill text-bg-primary">{{ $event->event_name }}</span>
                                    </td>
                                    <td>
                                        @if ($event->page_url)
                                            <a href="{{ $event->page_url }}" target="_blank" rel="noopener"
                                                title="{{ $event->page_url }}">
                                                {{ \Illuminate\Support\Str::limit(parse_url($event->page_url, PHP_URL_PATH) ?: $event->page_url, 40) }}
                                            </a>
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td class="w-min">
                                        @if ($event->user)
                                            {{ $event->user->name }}
                                            <br>
                                            <small class="text-muted">{{ $event->user->email }}</small>
                                        @elseif ($event->guest_name || $event->guest_email)
                                            {{ $event->guest_name }}
                                            <br>
                                            <small class="text-muted">{{ $event->guest_email }} ({{ __('Guest') }})</small>
                                        @else
                                            <span class="text-muted">{{ __('Guest') }}</span>
                                        @endif
                                    </td>
                                    <td>{{ $event->product_name ?: '—' }}</td>
                                    <td class="w-min">{{ $event->shop?->name ?? $event->shop_name ?? '—' }}</td>
                                    <td class="w-min">
                                        @if (! is_null($event->value))
                                            {{ number_format($event->value, 2) }} {{ $event->currency }}
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td class="w-min">
                                        {{ $event->ip_address ?? '—' }}
                                        <br>
                                        <small class="text-muted" title="{{ $event->user_agent }}">
                                            {{ \Illuminate\Support\Str::limit($event->user_agent, 30) }}
                                        </small>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="100%" class="text-center">
                                        {{ __('No events found') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="my-3">
            {{ $events->links() }}
        </div>
    </div>
@endsection
