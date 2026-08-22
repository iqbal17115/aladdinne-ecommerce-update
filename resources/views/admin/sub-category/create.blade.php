@extends('layouts.app')

@section('header-title', __('Create New Sub Category'))

@section('content')
    <div class="page-title">
        <div class="d-flex gap-2 align-items-center">
            <i class="fa-solid fa-border-all"></i> {{__('Create New Sub Category')}}
        </div>
    </div>
    <form action="{{ route('admin.subcategory.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="row">
            <div class="col-lg-8 m-auto">
                <div class="card mt-3">
                    <div class="card-body">

                        <div class="d-flex gap-2 border-bottom pb-2">
                            <i class="fa-solid fa-user"></i>
                            <h5>
                                {{ __('Sub Category Information') }}
                            </h5>
                        </div>

                        <div class="mt-3">
                            <label class="form-label">
                                {{ __('Select Category') }}
                                <span class="text-danger">*</span>
                            </label>
                            <select name="category[]" data-placeholder="Select Category" class="form-control select2"
                                multiple style="width: 100%" required>
                                <option value="" disabled>
                                    {{ __('Select Category') }}
                                </option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                            @error('category')
                                <p class="text text-danger m-0">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mt-3">
                            <x-input label="Name" name="name" type="text" placeholder="Name" required="true" />
                        </div>

                        <div class="mt-3">
                            <x-input label="Name (2nd Language)" name="name_ar" type="text" placeholder="Arabic Name" :value="old('name_ar')" />
                        </div>

                        <div class="mt-3">
                            <label for="short_description" class="form-label">{{ __('Short Description') }}</label>
                            <textarea name="short_description" id="short_description"
                                class="form-control @error('short_description') is-invalid @enderror" rows="3"
                                placeholder="{{ __('Enter short description') }}">{{ old('short_description') }}</textarea>
                            @error('short_description')
                                <p class="text text-danger m-0">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mt-3">
                            <label for="short_description_ar" class="form-label">{{ __('Short Description (2nd Language)') }}</label>
                            <textarea name="short_description_ar" id="short_description_ar"
                                class="form-control @error('short_description_ar') is-invalid @enderror" rows="3"
                                placeholder="{{ __('Enter Arabic short description') }}">{{ old('short_description_ar') }}</textarea>
                            @error('short_description_ar')
                                <p class="text text-danger m-0">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mt-3">
                            <x-image-picker name="thumbnail" />
                        </div>

                        <div class="mt-5 d-flex gap-2 justify-content-between flex-wrap">
                            <a href="{{ route('admin.category.index') }}" class="btn btn-secondary py-2 px-4">
                                {{__('Back')}}
                            </a>

                            <button type="submit" class="btn btn-primary py-2 px-4">
                                {{__('Submit')}}
                            </button>

                        </div>

                    </div>

                </div>
            </div>
        </div>

    </form>
@endsection
