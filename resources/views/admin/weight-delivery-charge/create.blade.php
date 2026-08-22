@extends('layouts.app')
@section('header-title', __('Add New Delivery Charge'))

@section('content')
    <div class="page-title">
        <div class="d-flex gap-2 align-items-center">
            <i class="fa-solid fa-car"></i> {{ __(' New Delivery Charge') }}
        </div>
    </div>
    <form action="{{ route('admin.weightWiseDeliveryCharge.store') }}" method="POST">
        @csrf
        <div class="row">
            <div class="col-xl-9 mx-auto">
                <div class="card mt-3">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <x-input label="Minimum Weight (kg)" name="min_weight" type="text"
                                    placeholder="Enter Minimum Order Weight" onlyNumber required="true" />
                            </div>
                            <div class="col-md-6">
                                <x-input label="Maximum Weight (kg)" name="max_weight" type="text"
                                    placeholder="Enter Maximum Order Weight" onlyNumber required="true" />
                            </div>
                            <div class="col-md-6 mt-3">
                                <x-input label="Charge" name="delivery_charge" type="text"
                                    placeholder="Enter Delivery Charge" onlyNumber required="true" />
                            </div>
                        </div>
                        <div class="col-12 d-flex justify-content-end mt-4">
                            <button class="btn btn-primary py-2 px-5">
                                {{ __('Submit') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </form>
@endsection
