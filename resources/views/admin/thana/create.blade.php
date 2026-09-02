@extends('layouts.app')
@section('content')
    <div class="container-fluid">
        <div class="card">
            <div class="card-body">
                <h4>{{ __('Add New Thana') }}</h4>
                <form action="{{ route('admin.thana.store') }}" method="POST">
                    @csrf

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">{{ __('Area') }} <span class="text-danger">*</span></label>
                            <select name="area_id" class="form-control" required>
                                <option value="" disabled selected>{{ __('Select Area') }}</option>
                                @foreach ($areas as $area)
                                    <option value="{{ $area->id }}">{{ $area->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4 mb-3">
                            <x-input type="text" name="name" label="Thana Name" placeholder="Thana Name" required="true" />
                        </div>

                        <div class="col-md-4 mb-3">
                            <x-input type="number" step="0.01" name="shipping_charge" label="Shipping Charge"
                                placeholder="Shipping Charge" required="true" onlyNumber="true" />
                        </div>

                        <div class="col-md-4 mt-4 form-check">
                            <input type="checkbox" class="form-check-input" name="is_active" id="is_active" checked>
                            <label class="form-check-label" for="is_active">{{ __('Active') }}</label>
                        </div>
                    </div>

                    <button class="btn btn-primary" type="submit">{{ __('Submit') }}</button>
                </form>
            </div>
        </div>
    </div>
@endsection
