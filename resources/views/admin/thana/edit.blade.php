@extends('layouts.app')
@section('content')
    <div class="container-fluid">
        <div class="card">
            <div class="card-body">
                <h4>{{ __('Update Thana') }}</h4>
                <form action="{{ route('admin.thana.update', $thana->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <x-select label="District / Area" name="area_id" placeholder="Select District / Area" required="true">
                                <option value="" disabled>{{ __('Select District / Area') }}</option>
                                @foreach ($areas as $area)
                                    <option value="{{ $area->id }}" {{ $thana->area_id == $area->id ? 'selected' : '' }}>
                                        {{ $area->name }}
                                    </option>
                                @endforeach
                            </x-select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <x-input type="text" name="name" label="Thana Name" placeholder="Thana Name"
                                :value="$thana->name" required="true" />
                        </div>

                        <div class="col-md-3 mt-1 form-check">
                            <input type="checkbox" class="form-check-input" name="is_active" id="is_active"
                                {{ $thana->is_active ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">{{ __('Active') }}</label>
                        </div>
                    </div>

                    <button class="btn btn-primary" type="submit">{{ __('Update') }}</button>
                </form>
            </div>
        </div>
    </div>
@endsection
