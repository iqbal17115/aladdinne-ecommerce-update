@extends('layouts.app')
@section('header-title', __('Bulk Product Exports'))
@section('content')
    <div class="d-flex align-items-center flex-wrap gap-3 justify-content-between px-3">
        <h4>
            {{ __('Bulk Product Exports ') }}
            <h2 class="accordion-header">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="modal"
                    data-bs-target="#showInstraction" aria-expanded="false" aria-controls="showInstraction">
                    {{ __('Get instructions') }}
                    <span class="info ms-2" data-bs-toggle="tooltip" data-bs-placement="top"
                        data-bs-title="{{ __('Get instructions for bulk import') }}">
                        <i class="bi bi-info"></i>
                    </span>
                </button>
            </h2>
        </h4>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="showInstraction" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
            <h5 class="modal-title" id="staticBackdropLabel">Bulk export instrctions</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row g-4">
                    <!-- Step 1 -->
                    <div class="col-12">
                        <div class="export-steps-item h-100">
                            <div class="d-flex gap-3 justify-content-between align-items-center">
                                <div>
                                    <h3 class="fz-20 text-dark">
                                        {{ __('Step 1') }}
                                    </h3>
                                    <div>
                                        {{__('Select Data Type')}}
                                    </div>
                                </div>
                                <img src="{{ asset('assets/images/bulk-export-1.png') }}" alt="">
                            </div>

                            <h4 class="mt-3 text-dark fz-20">
                                {{__('Instruction')}}
                            </h4>

                            <ul class="m-0 pl-4">
                                <li>
                                    {{__('Choose the data type to specify the order in which you want your data sorted when downloading.')}}
                                </li>
                            </ul>
                        </div>
                    </div>

                    <!-- Step 2 -->
                    <div class="col-12">
                        <div class="export-steps-item h-100">
                            <div class="d-flex gap-3 justify-content-between align-items-center">
                                <div>
                                    <h3 class="fz-20 text-dark">
                                        {{ __('Step 2') }}
                                    </h3>
                                    <div>
                                        {{__('Select Data Range by All and Export')}}
                                    </div>
                                </div>
                                <img src="{{ asset('assets/images/bulk-export-2.png') }}" alt="">
                            </div>

                            <h4 class="mt-3 text-dark fz-20">
                                {{__('Instruction')}}
                            </h4>

                            <ul class="m-0 pl-4">
                                <li>
                                    {{ __('All Products downloads as .xlsx. Facebook Catalog is available as .csv or .xlsx with the same Meta catalog columns.') }}
                                </li>
                                <li>
                                    {{ __('Each size and color combination is exported as a separate variant row. If an image is missing, its image_link is left blank so the file can still be downloaded.') }}
                                </li>
                                <li>
                                    {{ __("Click 'Reset' to restore the default export type.") }}
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
            <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Understood</button>
            </div>
        </div>
        </div>
    </div>

    <div class="container-fluid mt-3">

        <div class="card" style="border-color: rgba(231, 234, 243, 0.5019607843);">
            <div class="card-body">
                <div class="d-flex gap-2 pb-2 mt-4">
                    <h5>
                        <i class="fa-solid fa-file-import"></i>
                        {{ __('Export Products') }}
                    </h5>
                </div>

                @hasPermission('shop.bulk-product-export.export')
                <form action="{{ route('shop.bulk-product-export.export') }}" method="POST" data-file-download>
                    @csrf
                    @if ($errors->has('type'))
                        <div class="alert alert-danger" role="alert">
                            {{ $errors->first('type') }}
                        </div>
                    @endif
                    <div class="row">
                        <div class="col-lg-4">
                            <label for="type" class="form-label">
                                {{ __('Type') }}
                            </label>
                            <select name="type" id="type" class="form-select form-control">
                                <option value="all">
                                    {{ __('All Products (Excel)') }}
                                </option>
                                <option value="facebook_catalog_csv">
                                    {{ __('Facebook Catalog (CSV)') }}
                                </option>
                                <option value="facebook_catalog_xlsx">
                                    {{ __('Facebook Catalog (Excel)') }}
                                </option>
                            </select>
                            <div class="form-text">
                                {{ __('Both Facebook formats use the same catalog columns and size/color variants. Products without saved images are exported with a blank image_link.') }}
                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-2 mt-3 justify-content-end">
                        <button type="reset" class="btn btn-secondary py-2 px-3">
                            {{ __('Reset') }}
                        </button>
                        <button type="submit" class="btn btn-primary py-2 px-3">
                          <img src="{{ asset('assets/icons-admin/download.svg') }}" alt="export" loading="lazy" />
                          {{ __('Export') }}
                        </button>
                    </div>
                </form>
                @endhasPermission
            </div>
        </div>

    </div>
@endsection
@push('css')
    <style>
        .export-steps-item ul li {
            margin-bottom: 6px;
        }
    </style>
@endpush

@push('scripts')
    <script src="{{ asset('assets/scripts/drop-zone.js') }}"></script>
@endpush
