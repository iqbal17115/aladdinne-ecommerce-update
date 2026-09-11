@extends('layouts.app')
@section('content')
    <div class="d-flex align-items-center flex-wrap gap-3 justify-content-between px-3">
        <h4>
            {{ __('Thana List') }}
        </h4>

        @hasPermission('admin.thana.create')
            <a href="{{ route('admin.thana.create') }}" class="btn py-2 btn-primary">
                <i class="fa fa-plus-circle"></i>
                {{ __('Add Thana') }}
            </a>
        @endhasPermission
    </div>

    <div class="container-fluid mt-3">

        <div class="mb-3 card">
            <div class="card-body">

                <form action="" class="d-flex align-items-center justify-content-between gap-3 mb-3 flex-column flex-md-row">
                    <div class="input-group" style="max-width: 400px">
                        <input type="text" name="search" class="form-control" placeholder="{{ __('Search by name') }}"
                            value="{{ request('search') }}">
                        <button type="submit" class="input-group-text btn btn-primary">
                            <i class="fa fa-search"></i> {{ __('Search') }}
                        </button>
                    </div>

                    <select name="area_id" class="form-control" style="max-width: 300px"
                        onchange="this.form.submit()">
                        <option value="">{{ __('All District / Area') }}</option>
                        @foreach ($areas as $area)
                            <option value="{{ $area->id }}" {{ request('area_id') == $area->id ? 'selected' : '' }}>
                                {{ $area->name }}
                            </option>
                        @endforeach
                    </select>
                </form>

                <div class="table-responsive">
                    <table class="table border table-responsive-lg">
                        <thead>
                            <tr>
                                <th class="text-center">{{ __('SL') }}</th>
                                <th>{{ __('Name') }}</th>
                                <th>{{ __('District / Area') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th class="text-center">{{ __('Action') }}</th>
                            </tr>
                        </thead>
                        @forelse($thanas as $key => $thana)
                            <tr>
                                <td class="text-center">{{ $thanas->firstItem() + $key }}</td>

                                <td>{{ $thana->name }}</td>

                                <td>{{ $thana->area?->name ?? 'N/A' }}</td>

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
                                        @hasPermission('admin.thana.edit')
                                            <a href="{{ route('admin.thana.edit', $thana->id) }}" class="btn btn-outline-primary circleIcon btn-sm">
                                                <img src="{{ asset('assets/icons-admin/edit.svg') }}" alt="edit" loading="lazy" />
                                            </a>
                                        @endhasPermission
                                        @hasPermission('admin.thana.destroy')
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
@endsection
