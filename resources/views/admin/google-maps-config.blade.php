@extends('layouts.app')

@section('header-title', __('Google Maps Configuration'))

@section('content')
    <div class="container-fluid my-4">
        <div class="row">
            <div class="col-xl-8 col-lg-9 m-auto">
                <form action="{{ route('admin.googleMaps.update') }}" method="POST">
                    @csrf
                    <div class="card">
                        <div class="card-header py-3">
                            <h4 class="m-0">{{ __('Google Maps Configuration') }}</h4>
                        </div>
                        <div class="card-body pb-4">
                            <div class="mb-3">
                                <x-input type="text" name="google_maps_key" label="GOOGLE MAPS API KEY" placeholder="GOOGLE MAPS API KEY" :value="config('services.google_maps.key')" />
                            </div>
                        </div>
                        @hasPermission('admin.googleMaps.update')
                            <div class="card-footer py-3">
                                <div class="d-flex justify-content-end">
                                    <button class="btn btn-primary py-2">{{ __('Save And Update') }}</button>
                                </div>
                            </div>
                        @endhasPermission
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
