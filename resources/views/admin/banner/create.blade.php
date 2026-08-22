@extends('layouts.app')

@section('header-title', __('Add New Banner'))

@section('content')
    <div class="page-title">
        <div class="d-flex gap-2 align-items-center">
            <i class="fa-solid fa-image"></i> {{__('Add New Banner')}}
        </div>
    </div>
    <form action="{{ route('admin.banner.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="row">

            <div class="col-span-12 col-xl-6">
                <div class="card mt-3 h-100">
                    <div class="card-body">
                        <div class="">
                            <x-input label="Title" name="title" type="text" placeholder="Enter Short Title" />
                        </div>

                        <div class="mt-4">
                             <x-image-picker name="banner" />
                        </div>

                        <div class="mt-4">
                            <x-input label="URL" name="url" type="text" placeholder="https://example.com or /category/slug" />
                        </div>

                        @if ($businessModel != 'single')
                            <div class="mt-4 border d-inline-flex align-items-center justify-content-center gap-2 p-2 rounded">
                                <label for="forShop" class="form-label mb-0 fw-bold">
                                    {{__('This Banner For Own Shop')}}
                                </label>
                                <input type="checkbox" name="for_shop" id="forShop" style="width: 20px; height: 20px" class="form-check-input m-0" />
                            </div>
                        @endif

                        <div class="col-12 d-flex justify-content-end mt-4">
                            <button class="btn btn-primary py-2 px-5">
                                {{__('Submit')}}
                            </button>
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </form>
@endsection
