@extends('layouts.app')
@section('header-title', __('PreOrder Settings'))
@section('content')
    <div class="row">
        <div class="col-xl-9">
            <form action="{{ route('shop.preOrder.setting.update') }}" method="POST">
                @csrf
                <div class="card">
                    <div class="card-body">
                        <div class="border mt-3 rounded-3">
                            <div class="d-flex align-items-center gap-2 border-bottom p-2">
                                <h5 class="fw-bold fz-16"><i class="fa-solid fa-caret-right"></i> {{ __('Enable Pre-Order') }}
                                </h5>
                            </div>
                            <div class="p-3">
                                <div class="row gy-3">
                                    <div class="col-lg-6">
                                        <div
                                            class="border rounded p-2 d-flex align-items-center justify-content-between gap-2 flex-wrap flex-grow-1">
                                            <label class="form-label m-0 fw-medium" for="otpVerify">
                                                {{ __('Enable Pre-Order') }}
                                            </label>
                                            <label class="switch mb-0">
                                                <input id="otpVerify" type="checkbox" value="1"
                                                    {{ $preOrderSetting?->pre_order_active ? 'checked' : '' }}
                                                    name="pre_order_active">
                                                <span class="slider round"></span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="border mt-4 rounded-3">
                            <div class="border-bottom p-2 fw-bold fz-16">
                                <i class="fa-solid fa-caret-right"></i> {{ __('Pre-Order For Shop') }}
                            </div>
                            <div class="p-3">
                                <div class="border rounded p-2 d-flex align-items-center justify-content-between gap-2 flex-wrap"
                                    style="max-width: 400px">
                                    <label class="form-label m-0 fw-medium" for="toggle">
                                        {{ __('Enable Pre-Order For Shop') }}
                                    </label>
                                    <label class="switch mb-0" data-bs-toggle="tooltip" data-bs-placement="left"
                                        data-bs-title="Optional/Required">
                                        <input id="toggle" type="checkbox" value="1"
                                            {{ $preOrderSetting?->pre_order_for_shop ? 'checked' : '' }}
                                            name="pre_order_for_shop">
                                        <span class="slider round"></span>
                                    </label>
                                </div>
                                <p class="text-muted text-sm-start">
                                    {{ __('if disable, then others shop pre-order product will be disable') }}</p>
                                <div class="row gy-3 mt-3">
                                    <div class="col-md-6">
                                        <x-input type="text" name="shop_commission" label="Shop Commission"
                                            :value="$preOrderSetting?->shop_commission ?? 0" onlyNumber="true" />
                                    </div>

                                    <div class="col-md-6">
                                        <label for="" class="form-label">{{ __('Commission Type') }}</label>
                                        <select name="commission_type" id="commissionType" class="form-control form-select">
                                            <option value="percentage" id="percentage"
                                                {{ $generaleSetting?->commission_type == 'percentage' ? 'selected' : '' }}{{ $generaleSetting?->commission_charge == 'monthly' ? 'disabled' : '' }}>
                                                {{ __('Percentage') }}
                                            </option>
                                            <option value="fixed"
                                                {{ $generaleSetting?->commission_type == 'fixed' ? 'selected' : '' }}>
                                                {{ __('Fixed Amount') }}
                                            </option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="border mt-4 rounded-3">
                            <div class="border-bottom p-2 fw-bold fz-16">
                                <i class="fa-solid fa-caret-right"></i> {{ __('Pre-Order Policy') }}
                            </div>
                            <div class="p-3">
                                <div class="row gy-3 mt-3">
                                    <div class="col-md-6">
                                        <label for="">
                                            {{ __('Pre-Order Policy') }}
                                            <span class="text-danger"></span>
                                        </label>
                                        <textarea name="pre_order_policy" class="form-control @error('pre_order_policy') is-invalid @enderror" rows="6"
                                            placeholder="Enter preorder notice">{{ $preOrderSetting?->pre_order_policy }}</textarea>
                                        <p class="text-muted text-sm-start">{{ __('maximum 300 words') }}</p>
                                        @error('pre_order_policy')
                                            <p class="text text-danger m-0">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label for="">
                                            {{ __('Pre-Order Refund Policy') }}
                                            <span class="text-danger"></span>
                                        </label>
                                        <textarea name="pre_order_refund_policy" class="form-control @error('pre_order_refund_policy') is-invalid @enderror"
                                            rows="6" placeholder="Enter preorder refund notice">{{ $preOrderSetting?->pre_order_refund_policy }}</textarea>
                                        <p class="text-muted text-sm-start">{{ __('maximum 300 words') }}</p>
                                        @error('pre_order_refund_policy')
                                            <p class="text text-danger m-0">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                        @hasPermission('shop.preOrder.setting.update')
                            <div class="d-flex justify-content-end mt-4">
                                <button type="submit" class="btn btn-primary py-2.5 px-3">
                                    {{ __('Save And Update') }}
                                </button>
                            </div>
                        @endhasPermission
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
