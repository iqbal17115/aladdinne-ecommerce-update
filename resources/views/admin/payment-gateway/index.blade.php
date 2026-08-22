@extends('layouts.app')

@section('header-title', __('Payment Gateways'))

@section('content')
    <div class="container-fluid mb-3">

        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
            <h4 class="m-0">{{ __('Payment Gateways') }}</h4>sdfsdf
        </div>

        <div class="row g-3 pg-wrapper">
            {{-- Left: gateway tab list --}}
            <div class="col-lg-4 col-xl-3">
                <div class="card pg-tab-card">
                    <div class="nav flex-column nav-pills pg-tabs" id="pg-tab" role="tablist"
                        aria-orientation="vertical">
                        @foreach ($paymentGateways as $index => $paymentGateway)
                            <button class="nav-link pg-tab {{ $loop->first ? 'active' : '' }}"
                                id="pg-tab-{{ $paymentGateway->id }}" data-bs-toggle="pill"
                                data-bs-target="#pg-pane-{{ $paymentGateway->id }}" type="button" role="tab"
                                aria-controls="pg-pane-{{ $paymentGateway->id }}"
                                aria-selected="{{ $loop->first ? 'true' : 'false' }}">
                                <img class="pg-tab-logo" src="{{ $paymentGateway->logo }}" alt="logo" loading="lazy" />
                                <span class="pg-tab-name">{{ ucwords($paymentGateway->title ?: $paymentGateway->name) }}</span>
                                <span class="pg-tab-dot {{ $paymentGateway->is_active ? 'on' : 'off' }}"></span>
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Right: form for the selected gateway --}}
            <div class="col-lg-8 col-xl-9">
                <div class="tab-content" id="pg-tabContent">
                    @foreach ($paymentGateways as $paymentGateway)
                        @php
                            $configs = json_decode($paymentGateway->config);
                        @endphp

                        <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}"
                            id="pg-pane-{{ $paymentGateway->id }}" role="tabpanel"
                            aria-labelledby="pg-tab-{{ $paymentGateway->id }}" tabindex="0">
                            <div class="card">
                                <div class="card-header d-flex align-items-center justify-content-between gap-2 py-3">
                                    <div class="d-flex align-items-center gap-3">
                                        <img id="preview{{ $paymentGateway->name }}" class="pg-header-logo"
                                            src="{{ $paymentGateway->logo }}" alt="logo" loading="lazy" />
                                        <p class="paymentTitle m-0">
                                            {{ strtoupper($paymentGateway->name) }}
                                        </p>
                                    </div>

                                    <div class="d-flex align-items-center gap-2">
                                        <span class="{{ $paymentGateway->is_active ? 'statusOn' : 'statusOff' }}">
                                            {{ $paymentGateway->is_active ? 'On' : 'Off' }}
                                        </span>
                                        @hasPermission('admin.paymentGateway.toggle')
                                            <label class="switch mb-0" data-bs-toggle="tooltip" data-bs-placement="left"
                                                data-bs-title="{{ $paymentGateway->is_active ? 'Turn off' : 'Turn on' }}">
                                                <a href="{{ route('admin.paymentGateway.toggle', $paymentGateway->id) }}"
                                                    class="confirm">
                                                    <input type="checkbox" {{ $paymentGateway->is_active ? 'checked' : '' }}>
                                                    <span class="slider round"></span>
                                                </a>
                                            </label>
                                        @endhasPermission
                                    </div>
                                </div>
                                <div class="card-body">
                                    <form action="{{ route('admin.paymentGateway.update', $paymentGateway->id) }}"
                                        method="POST" enctype="multipart/form-data">
                                        @csrf
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <x-select name="mode" label="Mode">
                                                    <option value="test" {{ $paymentGateway->mode == 'test' ? 'selected' : '' }}>
                                                        Test
                                                    </option>
                                                    <option value="live" {{ $paymentGateway->mode == 'live' ? 'selected' : '' }} {{ app()->environment('local') ? 'disabled' : '' }}>
                                                        Live
                                                    </option>
                                                </x-select>
                                            </div>

                                            <div class="col-md-6">
                                                <x-input name="title" type="text" label="Payment Gateway Title"
                                                    :value="$paymentGateway->title" required="true"
                                                    readonly="{{ app()->environment('local') ? 'true' : '' }}" />
                                            </div>

                                            @foreach ($configs as $key => $value)
                                                @if ($key != 'note')
                                                    @php
                                                        $label = ucwords(str_replace('_', ' ', $key));
                                                    @endphp

                                                    <div class="col-md-6">
                                                        <x-input :value="$value" name="config[{{ $key }}]" type="text"
                                                            placeholder="{{ $label }}" label="{{ $label }}"
                                                            required="true"
                                                            readonly="{{ app()->environment('local') ? 'true' : '' }}" />
                                                    </div>
                                                @else
                                                    <div class="col-12">
                                                        <div class="alert alert-warning mb-0">
                                                            {{ $value }}
                                                        </div>
                                                    </div>
                                                @endif
                                            @endforeach

                                            <div class="col-md-6">
                                                <x-file name="logo" label="Choose Logo"
                                                    preview="preview{{ $paymentGateway->name }}" />
                                            </div>

                                            @if ($paymentGateway->name == 'payu')
                                                <div class="col-12">
                                                    <p class="m-0 text-muted">Salt- 256 bit</p>
                                                </div>
                                            @endif
                                        </div>

                                        @hasPermission('admin.paymentGateway.update')
                                            <div class="mt-4 d-flex justify-content-end">
                                                <button type="submit" class="btn btn-primary py-2">
                                                    {{ __('Save And Update') }}
                                                </button>
                                            </div>
                                        @endhasPermission
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@endsection

@push('css')
    <style>
        .pg-tab-card {
            position: sticky;
            top: 90px;
            overflow: hidden;
        }

        .pg-tabs {
            display: flex;
            flex-direction: column;
            flex-wrap: nowrap;
            max-height: calc(100vh - 140px);
            overflow-y: auto;
        }

        .pg-tabs .pg-tab {
            width: 100%;
            min-height: 52px;
            display: flex;
            align-items: center;
            gap: 10px;
            text-align: left;
            border-radius: 0 !important;
            padding: 10px 16px !important;
            margin: 0 !important;
            color: #475569 !important;
            background: transparent !important;
            border: 0;
            border-left: 3px solid transparent !important;
            border-bottom: 1px solid #eef0f3 !important;
            transition: background .15s ease, color .15s ease;
        }

        .pg-tabs .pg-tab:last-child {
            border-bottom: 0 !important;
        }

        .pg-tabs .pg-tab:hover {
            background: #f6f7f9 !important;
            color: #1e293b !important;
        }

        .pg-tabs .pg-tab.active {
            background: #fff !important;
            color: var(--theme-color) !important;
            border-left-color: var(--theme-color) !important;
            font-weight: 600;
        }

        .pg-tabs .pg-tab-logo {
            height: 22px !important;
            width: 34px;
            max-width: 34px;
            object-fit: contain;
            flex-shrink: 0;
        }

        .pg-tabs .pg-tab-name {
            font-size: 15px;
            flex: 1 1 auto;
            min-width: 0;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .pg-tab-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            flex-shrink: 0;
        }

        .pg-tab-dot.on {
            background: #22c55e;
        }

        .pg-tab-dot.off {
            background: #cbd5e1;
        }

        .pg-header-logo {
            height: 42px;
            width: 60px;
            object-fit: contain;
        }

        .app-theme-dark .pg-tabs .pg-tab {
            color: #cbd5e1 !important;
            border-bottom-color: var(--dark-border-color) !important;
        }

        .app-theme-dark .pg-tabs .pg-tab:hover {
            background: rgba(255, 255, 255, .05) !important;
            color: #fff !important;
        }

        .app-theme-dark .pg-tabs .pg-tab.active {
            background: rgba(255, 255, 255, .04) !important;
            color: #fff !important;
            border-left-color: var(--theme-color) !important;
        }
    </style>
@endpush

@push('scripts')
    <script>
        $(".confirm").on("click", function(e) {
            e.preventDefault();
            const url = $(this).attr("href");
            Swal.fire({
                title: "Are you sure?",
                text: "You want to change status!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Yes, Change it!",
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = url;
                }
            });
        });
    </script>
@endpush
