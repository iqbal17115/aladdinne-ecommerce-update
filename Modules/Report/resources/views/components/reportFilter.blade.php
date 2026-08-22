<form action="">
    <div class="row">
        <div class="col-md-2 col-lg-2 col-12">
            <h4>{!! config('report.name') !!}</h4>
        </div>
        <div class="col-md-10 col-lg-10 col-12 ">
            <div class="mb-3 d-flex align-items-center justify-content-end flex-wrap gap-2">
                <div class="position-relative" style="max-width: 400px;">
                    <i class="fa fa-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                    <input type="text" name="search" class="form-control ps-5" placeholder="Search here"
                        value="{{ request('search') }}">
                </div>
                @if ($reportType != 'productSearch')
                    <div class="mx-2">
                        <select id="supplier" name="category_id" class="form-select">
                            <option value="">{{ __('Select Category') }}</option>
                            @foreach ($categories ?? [] as $category)
                                <option value="{{ $category->id }}"
                                    {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <div class=" mx-2">
                    <div class="input-group w-230">
                        <input type="text" id="reportrange" name="date_range" class="form-control"
                            value="{{ request('date_range') }}" placeholder="Filter by date" autocomplete="off">
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-primary" type="submit"><i class="fa fa-filter"></i>
                        {{ __('Filter') }}</button>
                    <a href="{{ route("shop.report.{$reportType}") }}" class="btn btn-dark"><i
                            class="fa fa-refresh"></i></a>
                    <a href="{{ route("shop.report.{$reportType}Export", request()->all()) }}"
                        class="btn btn-warning">{{ __('Export') }} <i class="fa fa-download"></i></a>
                    <a id="printBtn" onclick="printReport()" href="javascript:void(0)" class="btn btn-info"><i
                            class="fa fa-print"></i></a>
                </div>
            </div>
        </div>
    </div>
</form>
<div class="tab-content mb-3">
    @include('report::components.reportTab')
</div>
