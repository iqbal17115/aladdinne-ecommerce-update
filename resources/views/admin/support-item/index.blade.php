@extends('layouts.app')

@section('title', __('Support Items'))

@section('content')
    <div class="page-title">
        <div class="d-flex gap-2 align-items-center">
            <i class="bi bi-patch-check"></i> {{ __('Support Items') }}
        </div>
    </div>

    <div class="row g-4 mt-1">
        @foreach ($supportItems as $supportItem)
            <div class="col-lg-6">
                <form action="{{ route('admin.support-item.update', $supportItem) }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    <div class="card h-100">
                        <div class="card-header d-flex align-items-center justify-content-between border-bottom py-3">
                            <div class="d-flex align-items-center gap-2">
                                <img src="{{ $supportItem->icon_url }}" alt="icon" width="28" height="28">
                                <h6 class="mb-0">{{ $supportItem->title }}</h6>
                            </div>
                            <label class="switch mb-0">
                                <input type="hidden" name="is_active" value="0">
                                <input type="checkbox" name="is_active" value="1"
                                    {{ $supportItem->is_active ? 'checked' : '' }}>
                                <span class="slider round"></span>
                            </label>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label">{{ __('Icon') }}</label>
                                    <input type="file" name="icon" class="form-control" accept="image/*">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">{{ __('Title') }}</label>
                                    <input type="text" name="title" class="form-control"
                                        value="{{ old('title', $supportItem->title) }}" required>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">{{ __('Title (2nd Language)') }}</label>
                                    <input type="text" name="ar_title" class="form-control"
                                        value="{{ old('ar_title', $supportItem->ar_title) }}">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">{{ __('Description') }}</label>
                                    <input type="text" name="description" class="form-control"
                                        value="{{ old('description', $supportItem->description) }}" required>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">{{ __('Description (2nd Language)') }}</label>
                                    <input type="text" name="ar_description" class="form-control"
                                        value="{{ old('ar_description', $supportItem->ar_description) }}">
                                </div>
                            </div>

                            <div class="mt-4">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-lg"></i> {{ __('Update') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        @endforeach
    </div>
@endsection
