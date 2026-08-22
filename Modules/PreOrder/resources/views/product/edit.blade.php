@extends('layouts.app')
@section('header-title', __(' Edit Pre Order Product'))
@section('content')
    <div class="page-title">
        <div class="d-flex gap-2 align-items-center">
            {{ __(' Edit Pre Order Product') }}
        </div>
    </div>
    <form action="{{ route('shop.preOrder.product.update', $product->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="pb-2 fz-18 mt-3">
            {{ __('Product Info') }}
        </div>
        <div class="card p-2">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <div class="">
                            <x-input label="Product Name" name="name" id="product_name" type="text"
                                placeholder="Enter Product Name" :value="$product->name" required="true" />
                        </div>
                        <div class="mt-3">
                            <label for="basic-url" class="form-label mt-2">{{ __('Product Permalink/Slug') }}</label>
                            <div class="input-group mb-3">
                                <span class="input-group-text" id="basic-addon3">{{ config('app.url') }}/products/</span>
                                <input type="text" name="slug" value="{{ $product->slug }}"
                                    placeholder="your product permalink" class="form-control" id="basic-url"
                                    aria-describedby="basic-addon3">
                            </div>
                        </div>
                        @error('slug')
                            <p class="text text-danger m-0">{{ $message }}</p>
                        @enderror
                        <div class="mt-3 ">
                            <label for="">
                                {{ __('Short Description') }}
                                <span class="text-danger">*</span>
                            </label>
                            <textarea required name="short_description" class="form-control @error('short_description') is-invalid @enderror"
                                rows="2" placeholder="Enter short description">{{ old('short_description', $product->short_description) }}</textarea>
                            @error('short_description')
                                <p class="text text-danger m-0">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4 mb-3 mt-2">
                        <div class="card card-body h-100 custom-boxShadow" style="display:inline-block">
                            <div class="mb-2">
                                <h5>
                                    {{ __('Thumbnail') }}
                                    <span class="text-primary">{{ __('(Ratio 1280 x 960 px)') }}</span>
                                    <span class="text-danger">*</span>
                                </h5>
                                @error('thumbnail')
                                    <p class="text-danger">{{ $message }}</p>
                                @enderror
                            </div>
                            <x-image-picker name="thumbnail" :value="$product->product_thumbnail" />
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-8">
                        <div class="pb-2 fz-18 mt-4">
                            {{ __('Generale Information') }}
                        </div>
                        <div class="card custom-boxShadow">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4 col-lg-4 ">
                                        <x-select label="Select Brand" name="brand">
                                            <option value="">
                                                {{ __('Select Brand') }}
                                            </option>
                                            @foreach ($brands as $brand)
                                                <option value="{{ $brand->id }}"
                                                    {{ $brand->id == $product->brand_id ? 'selected' : '' }}>
                                                    {{ $brand->name }}</option>
                                            @endforeach
                                        </x-select>
                                    </div>
                                    <div class="col-lg-4 col-md-4 ">
                                        @if (($project ?? config('app.project_key')) === 'ReadyEcommerce')
                                            <x-select label="Select Unit" name="unit" required="true">
                                                <option value="">{{ __('Select Unit') }}</option>
                                                @foreach ($units ?? [] as $unit)
                                                    <option value="{{ $unit->id }}" @selected(old('unit', $product->unit_id) == $unit->id)>{{ $unit->name }}</option>
                                                @endforeach
                                            </x-select>
                                        @else
                                            <x-input type="text" name="unit" :value="$product->unit" label="Unit"
                                                placeholder="Unit" required="true" />
                                        @endif
                                    </div>
                                    <div class="col-md-4 col-lg-4">
                                        <label class="form-label d-flex align-items-center gap-2 justify-content-between">
                                            <div class="d-flex align-items-center gap-2">
                                                <span>
                                                    {{ __('Product SKU') }}
                                                    <span class="text-danger">*</span>
                                                </span>
                                                <span class="info" data-bs-toggle="tooltip" data-bs-placement="top"
                                                    data-bs-title="{{ __('Create a unique product code. This will be used generate barcode') }}">
                                                    <i class="bi bi-info"></i>
                                                </span>
                                            </div>
                                            <span class="text-primary cursor-pointer" onclick="generateCode()">
                                                {{ __('Generate Code') }}
                                            </span>
                                        </label>
                                        <input type="text" id="barcode" value="{{ $product->code }}" name="code"
                                            placeholder="Ex: 134543" class="form-control" required
                                            oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*?)\..*/g, '$1');" />
                                        @error('code')
                                            <p class="text text-danger m-0">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div class="col-lg-6 col-md-6 mt-2">
                                        <x-input type="text" name="expected_delivery_date" label="Expected Delivery Date"
                                            placeholder="10 hours ,3 days,7-10 days" required="true" :value="$product->expected_delivery_date" />
                                    </div>
                                    <div class="col-lg-3 col-md-3 col-6 mt-5">
                                        <div class="form-check">
                                            <label class="form-check-label" for="flexCheckChecked">
                                                <strong>{{ __('Available') }}</strong>
                                            </label>
                                            <input class="form-check-input" name="is_available" value="1"
                                                type="checkbox" id="flexCheckChecked"
                                                {{ $product->is_available === 1 ? 'checked' : '' }}>
                                        </div>
                                    </div>
                                    <div class="col-lg-3 col-md-3 col-6 mt-5">
                                        <div class="form-check">
                                            <label class="form-check-label" for="flexCheckChecked">
                                                <strong>{{ __('RefundAble') }}</strong>
                                            </label>
                                            <input class="form-check-input" name="is_refund" value="1"
                                                type="checkbox" id="flexCheckChecked"
                                                {{ $product->is_refund === 1 ? 'checked' : '' }}>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="pb-2 fz-18 mt-4">
                            {{ __('Price Information') }}
                        </div>
                        <div class="card mb-4 custom-boxShadow">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-lg-4 col-md-6">
                                        <x-input type="text" name="buy_price" label="Buying Price"
                                            placeholder="Buying Price" :value="$product->buy_price" required="true"
                                            onlyNumber="true" />
                                    </div>
                                    <div class="col-lg-4 col-md-6">
                                        <x-input type="text" name="price" label="Selling Price"
                                            placeholder="Selling Price" :value="$product->price" required="true"
                                            onlyNumber="true" />
                                    </div>
                                    <div class="col-lg-4 col-md-6 mt-lg-0  mt-3 ">
                                        <x-input type="text" name="discount_price" label="Discount Price"
                                            placeholder="Discount Price" :value="$product->discount_price" onlyNumber="true" />
                                    </div>
                                    <div class="col-lg-3 col-md-4 mt-3">
                                        <x-input type="number" min="1" name="min_order_quantity"
                                            label="Min Quantity" :value="$product->min_order_quantity" placeholder="Minimum Order Quantity" />
                                    </div>
                                    <div class="col-lg-3 col-md-4 mt-3">
                                        <x-input type="number" min="1" name="preorder_quantity_limit"
                                            label="Max Quantity" :value="$product->preorder_quantity_limit"
                                            placeholder="Preorder Quantity Limit" />
                                    </div>
                                    <div class="col-lg-4 col-md-4 mt-3">
                                        <x-input type="text" onlyNumber="true" name="prepay_amount"
                                            label="Prepay Amount" :value="$product->prepay_amount" placeholder=" prepay Amount" />
                                    </div>
                                    <div class="col-lg-2 col-md-4 mt-5">
                                        <div class="form-check">
                                            <label class="form-check-label" for="flexCheckChecked">
                                                <strong>{{ __('Prepay') }}</strong>
                                            </label>
                                            <input class="form-check-input" name="is_prepay" value="1"
                                                type="checkbox" id="flexCheckChecked"
                                                {{ $product->is_prepay === 1 ? 'checked' : '' }}>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        @if ($project === 'ReadyGrocery')
                            <div class="card h-100 custom-boxShadow" style="max-height: 430px;">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">{{ __('Categories') }}</h5>
                                    @error('categories')
                                        <p class="text text-danger m-0">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="card-body ps-0 overflow-auto">
                                    {!! $htmlTree !!}
                                </div>
                            </div>
                        @else
                            <div class="pb-2 fz-18 mt-4">
                                {{ __('Category Information') }}
                            </div>
                            <div class="card custom-boxShadow">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-12">
                                            <label class="form-label">
                                                {{ __('Select Category') }}
                                                <span class="text-danger">*</span>
                                            </label>
                                            <select name="category" class="form-control select2" style="width: 100%">
                                                <option value="" selected disabled>
                                                    {{ __('Select Category') }}
                                                </option>
                                                @foreach ($categories as $category)
                                                    <option value="{{ $category->id }}"
                                                        {{ $product->categories->contains('id', $category->id) ? 'selected' : '' }}>
                                                        {{ $category->name }}</option>
                                                @endforeach
                                            </select>
                                            @error('category')
                                                <p class="text text-danger m-0">{{ $message }}</p>
                                            @enderror
                                        </div>

                                        <div class="col-12 mt-3 mt-md-0">
                                            <label class="form-label">
                                                {{ __('Select Sub Categories') }}
                                            </label>
                                            <select name="sub_category[]" data-placeholder="Select Sub Category"
                                                class="form-control select2" multiple style="width: 100%">
                                                <option value="" disabled>{{ __('Select Sub Category') }}</option>
                                                @foreach ($subCategories as $subCategory)
                                                    <option value="{{ $subCategory->id }}"
                                                        {{ $product->subcategories->contains('id', $subCategory->id) ? 'selected' : '' }}>
                                                        {{ $subCategory->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('sub_category')
                                                <p class="text text-danger m-0">{{ $message }}</p>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="mt-3">
                            <label for="">
                                {{ __('Description') }}
                                <span class="text-danger">*</span>
                            </label>
                            <div id="editor" style="max-height: 1250px; overflow-y: auto">
                                {!! $product->description !!}
                            </div>
                            <input type="hidden" id="description" name="description"
                                value="{{ $product->description }}">
                            @error('description')
                                <p class="text text-danger m-0">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="col-12 mt-3">
                            <div class="card h-100 custom-boxShadow">
                                <div class="card-body">
                                    <div class="mb-2">
                                        <h5>
                                            {{ __('Additional Thumbnail') }}
                                            <span class="text-primary">{{ __('(Ratio 1280 x 960 px)') }}</span>
                                        </h5>
                                        @error('additionThumbnail')
                                            <p class="text-danger">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div class="d-flex flex-wrap gap-2" id="additionalElements">
                                        @foreach ($product->medias as $media)
                                            <div class="thumbnail-wrapper d-flex flex-column align-items-start gap-1">
                                                <button type="button"
                                                    class="delete btn btn-sm btn-outline-danger circleIcon m-1"
                                                    style="display:none">
                                                    <img src="{{ asset('assets/icons-admin/trash.svg') }}" loading="lazy"
                                                        alt="trash" />
                                                </button>
                                                <x-image-picker name="additionThumbnail[]" :value="($project ?? config('app.project_key')) === 'ReadyEcommerce' ? $media->thumbnail : $media->additional_thumbnail" />
                                            </div>
                                        @endforeach
                                        <div class="thumbnail-wrapper d-flex flex-column align-items-start gap-1">
                                            <button type="button"
                                                class="delete btn btn-sm btn-outline-danger circleIcon m-1"
                                                style="display: none">
                                                <img src="{{ asset('assets/icons-admin/trash.svg') }}" loading="lazy"
                                                    alt="trash" />
                                            </button>
                                            <x-image-picker name="additionThumbnail[]" />
                                        </div>
                                    </div>
                                    <template id="imagePickerTemplate">
                                        <div class="thumbnail-wrapper d-flex flex-column align-items-start gap-1">
                                            <button type="button"
                                                class="delete btn btn-sm btn-outline-danger circleIcon m-1"
                                                style="display:none">
                                                <img src="data:image/svg+xml;charset=utf-8,%3Csvg%20width%3D%2224%22%20height%3D%2224%22%20viewBox%3D%220%200%2024%2024%22%20fill%3D%22none%22%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%3E%0A%3Cpath%20d%3D%22M20%205.25H15.786C15.693%205.068%2015.621%204.862%2015.544%204.632L15.342%204.02499C15.138%203.41299%2014.565%203%2013.919%203H10.081C9.43499%203%208.862%203.41299%208.658%204.02499L8.45599%204.632C8.37899%204.862%208.307%205.068%208.214%205.25H4C3.586%205.25%203.25%205.586%203.25%206C3.25%206.414%203.586%206.75%204%206.75H20C20.414%206.75%2020.75%206.414%2020.75%206C20.75%205.586%2020.414%205.25%2020%205.25Z%22%20fill%3D%22%23ef4444%22%2F%3E%0A%3Cpath%20d%3D%22M14%2016.75C13.586%2016.75%2013.25%2016.414%2013.25%2016V11C13.25%2010.586%2013.586%2010.25%2014%2010.25C14.414%2010.25%2014.75%2010.586%2014.75%2011V16C14.75%2016.414%2014.414%2016.75%2014%2016.75Z%22%20fill%3D%22%23ef4444%22%2F%3E%0A%3Cpath%20d%3D%22M10%2016.75C9.586%2016.75%209.25%2016.414%209.25%2016V11C9.25%2010.586%209.586%2010.25%2010%2010.25C10.414%2010.25%2010.75%2010.586%2010.75%2011V16C10.75%2016.414%2010.414%2016.75%2010%2016.75Z%22%20fill%3D%22%23ef4444%22%2F%3E%0A%3Cpath%20opacity%3D%220.4%22%20d%3D%22M18.95%206.75L18.19%2018.2C18.08%2019.78%2017.25%2021%2015.19%2021H8.81004C6.75004%2021%205.92004%2019.78%205.81004%2018.2L5.05005%206.75H18.95Z%22%20fill%3D%22%23ef4444%22%2F%3E%0A%3C%2Fsvg%3E%0A"
                                                    loading="lazy" alt="trash" />
                                            </button>
                                            <x-image-picker name="additionThumbnail[]" />
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="card mt-4 custom-boxShadow">
                            <div class="card-body">
                                <div class="d-flex gap-2 border-bottom pb-1">
                                    <i class="fa-solid fa-play"></i>
                                    <h5>
                                        {{ __('Upload or Add Product Video') }}
                                    </h5>
                                </div>
                                <div class="mt-1 d-flex gap-2">
                                    <div class="mb-1">
                                        <label for="uploadType" class="form-label">
                                            {{ __('Select Video Type') }}
                                        </label>
                                        <select class="form-select" name="uploadVideo[type]" id="uploadType"
                                            onchange="toggleFields()">
                                            <option value="file"
                                                {{ $product->video?->type == 'file' ? 'selected' : '' }}>
                                                {{ __('Upload Video File') }}
                                            </option>
                                            <option value="youtube"
                                                {{ $product->video?->type == 'youtube' ? 'selected' : '' }}>
                                                {{ __('YouTube Link') }}
                                            </option>
                                            <option value="vimeo"
                                                {{ $product->video?->type == 'vimeo' ? 'selected' : '' }}>
                                                {{ __('Vimeo Link') }}
                                            </option>
                                            <option value="dailymotion"
                                                {{ $product->video?->type == 'dailymotion' ? 'selected' : '' }}>
                                                {{ __('Dailymotion Link') }}
                                            </option>
                                        </select>
                                    </div>
                                    <div class="mb-1 flex-grow-1" id="fileUploadField">
                                        <label for="productVideo" class="form-label">
                                            {{ __('Upload Product Video') }}
                                        </label>
                                        <input type="file" class="form-control" name="uploadVideo[file]"
                                            id="productVideo" accept="video/*">
                                        <small class="text-muted">
                                            {{ __('Supported formats: MP4, AVI, MOV, WMV') }}
                                        </small>
                                    </div>
                                    <div class="mb-3 d-none flex-grow-1" id="youtubeField">
                                        <label for="youtubeLink" class="form-label">
                                            {{ __('YouTube Video Link') }}
                                        </label>
                                        <textarea class="form-control" name="uploadVideo[youtube_url]" id="youtubeLink" rows="3"
                                            placeholder='<iframe width="560" height="315" src="https://www.youtube.com/embed/MxcgrT_Kdxw?si=V63-aJ-4tPZUEKyk" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>'>{{ $product->video?->type == 'youtube' ? $product->video->url : '' }}</textarea>
                                        <small
                                            class="text-muted">{{ __('Paste a valid YouTube video embed code') }}</small>
                                    </div>
                                    <div class="mb-3 d-none flex-grow-1" id="vimeoField">
                                        <label for="vimeoLink" class="form-label">
                                            {{ __('Vimeo Video Link') }}
                                        </label>
                                        <textarea name="uploadVideo[vimeo_url]" id="vimeoLink" class="form-control" rows="3"
                                            placeholder="please enter valid vimeo video embed code">{{ $product->video?->type == 'vimeo' ? $product->video->url : '' }}</textarea>
                                        <small class="text-muted">{{ __('Paste a valid Vimeo video embed code') }}</small>
                                    </div>
                                    <div class="mb-3 d-none flex-grow-1" id="dailymotionField">
                                        <label for="dailymotionLink" class="form-label">
                                            {{ __('Dailymotion Video Link') }}
                                        </label>
                                        <textarea name="uploadVideo[dailymotion_url]" id="dailymotionLink" class="form-control" rows="3"
                                            placeholder="please enter valid dailymotion video embed code">{{ $product->video?->type == 'dailymotion' ? $product->video->url : '' }}</textarea>
                                        <small
                                            class="text-muted">{{ __('Paste a valid Dailymotion video embed code') }}</small>
                                    </div>
                                </div>
                                @error('uploadVideo.file')
                                    <p class="text text-danger m-0">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                        <div class="card mt-2">
                            <div class="card-body">
                                <div class="d-flex gap-2 border-bottom pb-1">
                                    <i class="fa-solid fa-pen"></i>
                                    <h5>
                                        {{ __('Preorder Notice') }}
                                    </h5>
                                </div>
                                <div class="mt-1">
                                    <label for="">
                                        {{ __('Preorder Notice') }}
                                        <span class="text-danger">*</span>
                                    </label>
                                    <textarea required name="preorder_notice" class="form-control @error('preorder_notice') is-invalid @enderror"
                                        rows="4" placeholder="Enter preorder notice">{{ old('preorder_notice', $product->preorder_notice) }}</textarea>
                                    @error('preorder_notice')
                                        <p class="text text-danger m-0">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card mt-4 mb-3 custom-boxShadow">
                            <div class="card-body">
                                <div class="d-flex gap-2 border-bottom pb-2">
                                    <i class="fa-solid fa-square-poll-vertical"></i>
                                    <h5>
                                        {{ __('SEO Information') }}
                                    </h5>
                                </div>
                                <div class="mt-3">
                                    <label for="uploadType" class="form-label">
                                        {{ __('Meta Title') }}
                                    </label>
                                    <x-input name="meta_title" :value="$product->meta_title" type="text"
                                        placeholder="Meta Title" />
                                </div>
                                <div class="mt-3">
                                    <label for="uploadType" class="form-label">
                                        {{ __('Meta Description') }}
                                    </label>
                                    <textarea name="meta_description" type="text" placeholder="{{ __('Meta Description') }}" class="form-control">{{ old('meta_description', $product->meta_description) }}</textarea>
                                    @error('meta_description')
                                        <p class="text text-danger m-0">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div class="mt-3">
                                    <label for="tags" class="form-label">@lang('Meta Keywords')</label>
                                    <select id="tags" name="meta_keywords[]" class="form-control selectTags"
                                        multiple style="width: 100%">
                                        @foreach (old('meta_keywords', $metaKeywords) as $keyword)
                                            <option value="{{ $keyword }}" selected>{{ $keyword }}</option>
                                        @endforeach
                                    </select>
                                    <small>{{ __('Write keywords and Press enter to add new one') }}</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="d-flex gap-3 justify-content-end align-items-center my-3">
            <button type="submit" class="btn btn-lg btn-primary rounded py-2 px-5">
                {{ __('Submit') }}
            </button>
        </div>
    </form>
@endsection
@push('css')
    <style>
        .category-tree {
            list-style: none;
            font-size: 16px;
            color: #5b6e88;
        }

        .category-tree li {
            line-height: 1.6;
        }

        .category-tree li input {
            margin-right: 5px;
        }

        .circleIcon {
            position: absolute;
        }

        .thumbnail-wrapper {
            border: 2px dashed var(--theme-color);
        }

        .custom-boxShadow {
            box-shadow: 1px 4px 8px #eae9e9 !important;
        }
    </style>
@endpush
@push('scripts')
    {{-- product category --}}
    <script src="{{ asset('assets/scripts/productCategory.js') }}"></script>
    <!-- additional thumbnail script-->
    <script src="{{ asset('assets/scripts/additionalThumbnail.js') }}"></script>
    {{-- description editor --}}
    <script src="{{ asset('assets/scripts/descriptionEditor.js') }}"></script>

    <script>
        function toggleFields() {
            // Hide all fields
            document.getElementById('fileUploadField').classList.add('d-none');
            document.getElementById('youtubeField').classList.add('d-none');
            document.getElementById('vimeoField').classList.add('d-none');
            document.getElementById('dailymotionField').classList.add('d-none');

            // Get selected type
            const selectedType = document.getElementById('uploadType').value;

            // Show relevant field
            if (selectedType === 'file') {
                document.getElementById('fileUploadField').classList.remove('d-none');
            } else if (selectedType === 'youtube') {
                document.getElementById('youtubeField').classList.remove('d-none');
            } else if (selectedType === 'vimeo') {
                document.getElementById('vimeoField').classList.remove('d-none');
            } else if (selectedType === 'dailymotion') {
                document.getElementById('dailymotionField').classList.remove('d-none');
            }
        }
        $(document).ready(function() {
            toggleFields();
            $(".selectTags").select2({
                tags: true,
                placeholder: "{{ __('Write keywords and Press enter to add new one') }}"
            });
            // form submit loader
            $('form').on('submit', function() {
                var submitButton = $(this).find('button[type="submit"]');

                submitButton.prop('disabled', true);
                submitButton.removeClass('px-5');

                submitButton.html(`<div class="d-flex align-items-center gap-1">
                    <div class="spinner-border" role="status"></div>
                    <span>Submitting...</span>
                </div>`)
            });
        });
        const generateCode = () => {
            const code = document.getElementById('barcode');
            code.value = Math.floor(Math.random() * 900000) + 100000;
        }
    </script>
@endpush
