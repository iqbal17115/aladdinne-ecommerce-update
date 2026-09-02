@extends('layouts.app')
@section('content')
    <div class="d-flex align-items-center flex-wrap gap-3 justify-content-between px-3">
        <h4>
            {{ __('Thana & Shipping Charge') }}
        </h4>
    </div>

    <div class="container-fluid mt-3">

        <div class="mb-3 card">
            <div class="card-body">

                <form action="" class="d-flex align-items-center justify-content-between gap-3 mb-3 flex-column flex-md-row">
                    <div class="d-flex gap-2 flex-column flex-md-row" style="width: 100%; max-width: 600px">
                        <input type="text" name="search" class="form-control" placeholder="{{ __('Search by name') }}"
                            value="{{ request('search') }}">
                        <select name="area_id" class="form-control" onchange="this.form.submit()">
                            <option value="">{{ __('All Areas') }}</option>
                            @foreach ($areas as $area)
                                <option value="{{ $area->id }}" {{ (string) $areaId === (string) $area->id ? 'selected' : '' }}>
                                    {{ $area->name }}
                                </option>
                            @endforeach
                        </select>
                        <button type="submit" class="input-group-text btn btn-primary">
                            <i class="fa fa-search"></i> {{ __('Search') }}
                        </button>
                    </div>
                    <button type="button" class="btn py-2 btn-primary text-nowrap" data-bs-toggle="modal" data-bs-target="#createThana">
                        <i class="fa fa-plus-circle"></i>
                        {{ __('Add Thana') }}
                    </button>
                </form>

                <div class="table-responsive">
                    <table class="table border table-responsive-lg">
                        <thead>
                            <tr>
                                <th class="text-center">{{ __('SL') }}</th>
                                <th>{{ __('Area') }}</th>
                                <th>{{ __('Thana') }}</th>
                                <th>{{ __('Shipping Charge') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th class="text-center">{{ __('Action') }}</th>
                            </tr>
                        </thead>
                        @forelse($thanas as $key => $thana)
                            <tr>
                                <td class="text-center">{{ ++$key }}</td>
                                <td>{{ $thana->area?->name }}</td>
                                <td>{{ $thana->name }}</td>
                                <td>{{ $thana->shipping_charge }}</td>

                                <td>
                                    @hasPermission('admin.thana.toggle')
                                        <a href="{{ route('admin.thana.toggle', $thana->id) }}">
                                            <span class="badge {{ $thana->is_active ? 'bg-success' : 'bg-danger' }}">
                                                {{ $thana->is_active ? __('Active') : __('Inactive') }}
                                            </span>
                                        </a>
                                    @else
                                        <span class="badge {{ $thana->is_active ? 'bg-success' : 'bg-danger' }}">
                                            {{ $thana->is_active ? __('Active') : __('Inactive') }}
                                        </span>
                                    @endhasPermission
                                </td>
                                <td class="text-center">
                                    <div class="d-flex gap-2 justify-content-center">
                                        @hasPermission('admin.thana.create')
                                            <a href="javascript:void(0)" class="btn btn-outline-primary circleIcon btn-sm"
                                                onclick='openThanaUpdateModal(@json($thana))'>
                                                <img src="{{ asset('assets/icons-admin/edit.svg') }}" alt="edit" loading="lazy" />
                                            </a>
                                        @endhasPermission
                                        @hasPermission('admin.thana.delete')
                                            <a href="{{ route('admin.thana.destroy', $thana->id) }}"
                                                class="circleIcon btn btn-outline-danger btn-sm deleteConfirm">
                                                <img src="{{ asset('assets/icons-admin/trash.svg') }}" alt="delete"
                                                    loading="lazy" />
                                            </a>
                                        @endhasPermission
                                    </div>
                                </td>
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
            {{ $thanas->links() }}
        </div>

    </div>

    <!--=== Create Thana Modal ===-->
    <form action="{{ route('admin.thana.store') }}" method="POST">
        @csrf
        <div class="modal fade" id="createThana">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            {{ __('Add New Thana') }}
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">

                        <div class="mb-3">
                            <label class="form-label">{{ __('Area') }} <span class="text-danger">*</span></label>
                            <select name="area_id" class="form-control" required>
                                <option value="" disabled selected>{{ __('Select Area') }}</option>
                                @foreach ($areas as $area)
                                    <option value="{{ $area->id }}" {{ (string) $areaId === (string) $area->id ? 'selected' : '' }}>
                                        {{ $area->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <x-input type="text" name="name" label="Thana Name" placeholder="Thana Name" required="true" />
                        </div>

                        <div class="mb-3">
                            <x-input type="number" step="0.01" name="shipping_charge" label="Shipping Charge"
                                placeholder="Shipping Charge" required="true" onlyNumber="true" />
                        </div>

                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" name="is_active" id="is_active" checked>
                            <label class="form-check-label" for="is_active">{{ __('Active') }}</label>
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

    <!--=== Update Thana Modal ===-->
    <form action="" id="updateThanaForm" method="POST">
        @csrf
        @method('PUT')
        <div class="modal fade" id="updateThana" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            {{ __('Update Thana') }}
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">{{ __('Area') }} <span class="text-danger">*</span></label>
                            <select name="area_id" id="update_thana_area_id" class="form-control" required>
                                @foreach ($areas as $area)
                                    <option value="{{ $area->id }}">{{ $area->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <x-input type="text" id="update_thana_name" name="name" label="Thana Name"
                                placeholder="Thana Name" required="true" />
                        </div>

                        <div class="mb-3">
                            <x-input type="number" step="0.01" id="update_thana_shipping_charge" name="shipping_charge"
                                label="Shipping Charge" placeholder="Shipping Charge" required="true" onlyNumber="true" />
                        </div>

                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" name="is_active" id="update_thana_is_active">
                            <label class="form-check-label" for="update_thana_is_active">{{ __('Active') }}</label>
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
        const openThanaUpdateModal = (thana) => {
            $("#update_thana_area_id").val(thana.area_id);
            $("#update_thana_name").val(thana.name);
            $("#update_thana_shipping_charge").val(thana.shipping_charge);
            $("#update_thana_is_active").prop('checked', !!thana.is_active);
            $("#updateThanaForm").attr('action', `{{ route('admin.thana.update', ':id') }}`.replace(':id', thana.id));

            $("#updateThana").modal('show');
        }
    </script>
@endpush
