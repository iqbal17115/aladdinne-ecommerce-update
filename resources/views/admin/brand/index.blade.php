@extends('layouts.app')
@section('header-title', __('Brands'))

@section('content')
    <div class="d-flex align-items-center flex-wrap gap-3 justify-content-between px-3">

        <h4>
            {{ __('Brand List') }}
        </h4>

        @hasPermission('admin.brand.create')
            <button type="button" data-bs-toggle="modal" data-bs-target="#createBrand" class="btn py-2 btn-primary">
                <i class="fa fa-plus-circle"></i>
                {{ __('Add Brand') }}
            </button>
        @endhasPermission
    </div>

    <div class="container-fluid mt-3">

        <div class="mb-3 card">
            <div class="card-body">
                <div class="cardTitleBox">
                    <h5 class="card-title chartTitle">
                        {{ __('Brands') }}
                    </h5>
                </div>
                <div class="table-responsive">
                    <table class="table border-left-right table-responsive-md">
                        <thead>
                            <tr>
                                <th class="text-center">{{ __('SL') }}</th>
                                <th>{{ __('Image') }}</th>
                                <th>{{ __('Name') }}</th>
                                @hasPermission('admin.brand.toggle')
                                    <th>{{ __('Status') }}</th>
                                @endhasPermission
                                @hasPermission('admin.brand.edit')
                                    <th class="text-center">{{ __('Action') }}</th>
                                @endhasPermission
                            </tr>
                        </thead>
                        @forelse($brands as $key => $brand)
                            @php
                                $serial = $brands->firstItem() + $key;
                            @endphp
                            <tr>
                                <td class="text-center">{{ $serial }}</td>
                                <td>
                                    <img src="{{ $brand->thumbnail }}" alt="{{ $brand->name }}" width="48"
                                        height="48" class="rounded object-fit-cover" loading="lazy" />
                                </td>
                                <td>{{ $brand->name }}</td>
                                @hasPermission('admin.brand.toggle')
                                    <td>
                                        <label class="switch mb-0">
                                            <a href="{{ route('admin.brand.toggle', $brand->id) }}">
                                                <input type="checkbox" {{ $brand->is_active ? 'checked' : '' }}>
                                                <span class="slider round"></span>
                                            </a>
                                        </label>
                                    </td>
                                @endhasPermission
                                @hasPermission('admin.brand.edit')
                                    <td class="text-center">
                                        <div class="d-flex gap-3 justify-content-center">
                                            <button type="button" class="btn btn-outline-primary btn-sm circleIcon"
                                                onclick="openUpdateModal(this)"
                                                data-id="{{ $brand->id }}"
                                                data-name="{{ $brand->name }}"
                                                data-name-ar="{{ $brand->name_ar }}"
                                                data-thumbnail="{{ $brand->getRawOriginal('brand_thumbnail') }}"
                                                data-thumbnail-url="{{ $brand->thumbnail }}">
                                                <img src="{{ asset('assets/icons-admin/edit.svg') }}" alt="edit" loading="lazy" />
                                            </button>

                                        </div>
                                    </td>
                                @endhasPermission
                            </tr>
                        @empty
                            <tr>
                                <td class="text-center" colspan="100%">{{ __('No Data Found') }}</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="my-3">
            {{ $brands->withQueryString()->links() }}
        </div>

    </div>


    <!--=== Create Brand Modal ===-->
    <form action="{{ route('admin.brand.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="modal fade" id="createBrand" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            {{ __('Create Brand') }}
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">

                        <div class="mb-3">
                            <label for="name" class="form-label">{{ __('Name') }} *</label>
                            <input type="text" class="form-control" id="name" name="name"
                                placeholder="Enter Name" required />
                            @error('name')
                                <p class="text text-danger m-0">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="name_ar" class="form-label">{{ __('Name (2nd Language)') }}</label>
                            <input type="text" class="form-control" id="name_ar" name="name_ar"
                                placeholder="Enter Arabic name" value="{{ old('name_ar') }}" />
                            @error('name_ar')
                                <p class="text text-danger m-0">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <x-image-picker name="thumbnail" />
                            @error('thumbnail')
                                <p class="text text-danger m-0">{{ $message }}</p>
                            @enderror
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            {{ __('Close') }}
                        </button>
                        <button type="submit" class="btn btn-primary">
                            {{ __('Submit') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <!--=== Edit Brand Modal ===-->
    <form action="" id="formEditBrand" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="modal fade" id="updateBrand" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            {{ __('Edit Brand') }}
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body" style="text-align: left">
                        <div class="mb-3">
                            <label for="name" class="form-label">{{ __('Name') }} *</label>
                            <input type="text" class="form-control" id="editName" name="name"
                                placeholder="Enter Name" value="" required />
                            @error('name')
                                <p class="text text-danger m-0">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="editNameAr" class="form-label">{{ __('Name (2nd Language)') }}</label>
                            <input type="text" class="form-control" id="editNameAr" name="name_ar"
                                placeholder="Enter Arabic name" value="" />
                            @error('name_ar')
                                <p class="text text-danger m-0">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <x-image-picker name="thumbnail" />
                            @error('thumbnail')
                                <p class="text text-danger m-0">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            {{ __('Close') }}
                        </button>
                        <button type="submit" class="btn btn-primary">
                            {{ __('Update') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection
@push('scripts')
    <script>
        const openUpdateModal = (button) => {
            const id = button.dataset.id;
            const name = button.dataset.name;
            const nameAr = button.dataset.nameAr ?? '';
            const thumbnail = button.dataset.thumbnail ?? '';
            const thumbnailUrl = button.dataset.thumbnailUrl ?? '{{ asset('default/default.jpg') }}';

            $("#editName").val(name);
            $("#editNameAr").val(nameAr);
            $("#formEditBrand").attr('action', `{{ route('admin.brand.update', ':id') }}`.replace(':id', id));
            $("#formEditBrand").find(".thumbnailPath").val(thumbnail);
            $("#formEditBrand").find(".thumbnailAdd").val(thumbnail ? thumbnail.split('/').pop() : '');
            $("#formEditBrand").find(".holder img").attr('src', thumbnailUrl);

            $("#updateBrand").modal('show');
        }
    </script>
@endpush
