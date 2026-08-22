@extends('layouts.app')

@section('title', __('Courier Settings'))

@section('content')
    <div class="page-title">
        <div class="d-flex gap-2 align-items-center">
            <i class="bi bi-truck"></i> {{ __('Courier Settings') }}
        </div>
    </div>

    <form action="{{ route('admin.delivery-setting.update') }}" method="POST">
        @csrf

        <div class="card mt-4">
            <div class="card-header d-flex align-items-center gap-2 border-bottom py-3">
                <i class="bi bi-sliders"></i>
                <h5 class="mb-0">{{ __('Delivery Charge Rules') }}</h5>
            </div>
            <div class="card-body">
                <div class="row g-4">

                    <div class="col-lg-4">
                        <div class="border rounded p-3 h-100">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <label class="form-label fw-semibold mb-0">{{ __('Weight Based Charge') }}</label>
                                <label class="switch mb-0">
                                    <input type="hidden" name="is_weight_charge" value="0">
                                    <input type="checkbox" name="is_weight_charge" value="1"
                                        {{ $generaleSetting?->is_weight_charge ? 'checked' : '' }}>
                                    <span class="slider round"></span>
                                </label>
                            </div>
                            <p class="text-muted small mb-0">
                                {{ __('Enable delivery charge calculation based on product weight from weight delivery charges table.') }}
                            </p>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="border rounded p-3 h-100">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <label class="form-label fw-semibold mb-0">{{ __('Distance & Duration Charge') }}</label>
                                <label class="switch mb-0">
                                    <input type="hidden" name="is_distance_charge" value="0">
                                    <input type="checkbox" name="is_distance_charge" value="1"
                                        {{ $generaleSetting?->is_distance_charge ? 'checked' : '' }}>
                                    <span class="slider round"></span>
                                </label>
                            </div>
                            <p class="text-muted small mb-0">
                                {{ __('Enable delivery charge calculation based on distance and duration from area settings.') }} <br>

                                <strong class="text-danger">{{__('Note:For proper functionality, ensure Google Maps API key is configured.')}}</strong>
                            </p>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="border rounded p-3 h-100">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <label class="form-label fw-semibold mb-0">{{ __('Free Delivery') }}</label>
                                <label class="switch mb-0">
                                    <input type="hidden" name="is_delivery_free" value="0">
                                    <input type="checkbox" name="is_delivery_free" value="1"
                                        {{ $generaleSetting?->is_delivery_free ? 'checked' : '' }}>
                                    <span class="slider round"></span>
                                </label>
                            </div>
                            <p class="text-muted small mb-0">
                                {{ __('Make all delivery charges free regardless of other settings.') }}
                            </p>
                        </div>
                    </div>

                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg"></i> {{ __('Update Settings') }}
                    </button>
                </div>
            </div>
        </div>
    </form>
@endsection
