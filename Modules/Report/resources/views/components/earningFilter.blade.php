<form action="">
    <div class="row">
        <div class="col-md-2 col-lg-2 col-12">
            <h4>{{ __("$title") }}</h4>
        </div>
        <div class="col-md-10 col-lg-10 col-12 ">
            <div class="mb-3 d-flex align-items-center justify-content-end flex-wrap gap-2">
                @if ($reportType == 'commissionList')
                    <div class="position-relative" style="max-width: 400px;">
                        <i class="fa fa-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                        <input type="text" name="search" class="form-control ps-5" placeholder="Search here"
                            value="{{ request('search') }}">
                    </div>
                @endif
                <div class=" mx-2">
                    <div class="date-range-wrapper d-flex align-items-center gap-2">
                        <div class="date-input">
                            <input type="text" id="datePicker" class="form-control datePickerfield" name="from"
                                value="{{ $from }}" placeholder="mm/dd/yyyy" />
                            <i class="bi bi-calendar3 dateIcon"></i>
                        </div>
                        <span class="date-separator">-</span>
                        <div class="date-input">
                            <input type="text" id="datePicker2" class="form-control datePickerfield" name="to"
                                value="{{ $to }}" placeholder="mm/dd/yyyy" />
                            <i class="bi bi-calendar3 dateIcon"></i>
                        </div>
                    </div>

                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-primary" type="submit"><i class="fa fa-filter"></i>
                        {{ __('Filter') }}</button>
                    <a href="{{ route("shop.report.{$reportType}") }}" class="btn btn-dark"><i
                            class="fa fa-refresh"></i></a>
                    @if ($reportType == 'commissionList')
                        <a href="{{ route("shop.report.{$reportType}Export", request()->all()) }}"
                            class="btn btn-warning">{{ __('Export') }} <i class="fa fa-download"></i></a>
                    @endif
                    <a id="printBtn" onclick="printReport()" href="javascript:void(0)" class="btn btn-info"><i
                            class="fa fa-print"></i></a>
                </div>
            </div>
        </div>
    </div>
</form>
