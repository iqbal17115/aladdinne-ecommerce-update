@extends('layouts.app')

@section('header-title', __('Courier Configuration'))

@section('content')
    <div class="container-fluid my-4">
        <div class="row">
            <!-- Courier Configuration -->
            <div class="col-xl-8 col-lg-9 mx-auto">
                <form action="{{ route('admin.courierConfig.update') }}" method="POST">
                    @method('put')
                    @csrf
                    <div class="card">
                        <div class="card-header py-3 d-flex align-items-center justify-content-between">
                            <h4 class="m-0">{{ __('SteadFast Courier Configuration') }}</h4>
                            <label class="switch mb-0" title="{{ __('Active / Inactive') }}">
                                <input type="hidden" name="is_active" value="0">
                                <input type="checkbox" name="is_active" value="1" {{ config('steadfast.active') ? 'checked' : '' }}>
                                <span class="slider round"></span>
                            </label>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-lg-6 mb-4">
                                    <x-input :value="config('steadfast.api_key')" name="api_key" type="text"
                                        placeholder="{{ __('SteadFast API Key') }}" label="{{ __('API Key') }}"
                                        required />
                                </div>
                                <div class="col-lg-6 mb-4">
                                    <x-input :value="config('steadfast.secret_key')" name="secret_key" type="text"
                                        placeholder="{{ __('SteadFast Secret Key') }}" label="{{ __('Secret Key') }}"
                                        required />
                                </div>
                                <div class="col-lg-6 mb-4">
                                    <x-input :value="config('steadfast.base_url')" name="base_url" type="text"
                                        placeholder="https://portal.packzy.com/api/v1" label="{{ __('Base URL') }}"
                                        required />
                                </div>
                                <div class="col-lg-6 mb-4">
                                    <label class="form-label">{{ __('Default Delivery Type') }}</label>
                                    <select name="default_delivery_type" class="form-control">
                                        <option value="0" {{ (int) config('steadfast.default_delivery_type') === 0 ? 'selected' : '' }}>
                                            {{ __('Home Delivery') }}
                                        </option>
                                        <option value="1" {{ (int) config('steadfast.default_delivery_type') === 1 ? 'selected' : '' }}>
                                            {{ __('Point Delivery') }}
                                        </option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        @hasPermission('admin.courierConfig.update')
                            <div class="card-footer">
                                <button type="submit" class="btn btn-primary py-2">{{ __('Save And Update') }}</button>
                            </div>
                        @endhasPermission
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
