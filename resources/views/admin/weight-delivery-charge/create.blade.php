@extends('layouts.app')
@section('header-title', __('Add New Delivery Charge'))

@section('content')
    <div class="page-title">
        <div class="d-flex gap-2 align-items-center">
            <i class="fa-solid fa-car"></i> {{ __(' New Delivery Charge') }}
        </div>
    </div>
    <form action="{{ route('admin.weightWiseDeliveryCharge.store') }}" method="POST">
        @csrf
        <div class="row">
            <div class="col-xl-10 mx-auto">
                <div class="card mt-3">
                    <div class="card-body">
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label">{{ __('Area') }}</label>
                                <select name="area_id" class="form-select">
                                    <option value="">{{ __('Global / All Areas') }}</option>
                                    @foreach ($areas as $area)
                                        <option value="{{ $area->id }}">{{ $area->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="mb-0">{{ __('Weight Ranges') }}</h6>
                            <button type="button" class="btn btn-sm btn-outline-primary" id="addWeightRangeBtn" onclick="addWeightRangeRow()">
                                <i class="fa fa-plus"></i> {{ __('Add Range') }}
                            </button>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered align-middle no-row-animation">
                                <thead>
                                    <tr>
                                        <th>{{ __('Min Weight (kg)') }}</th>
                                        <th>{{ __('Max Weight (kg)') }}</th>
                                        <th>{{ __('Delivery Charge') }}</th>
                                        <th class="text-center" style="width: 110px;">{{ __('Action') }}</th>
                                    </tr>
                                </thead>
                                <tbody id="weightRangeBody">
                                    <tr class="weight-range-row">
                                        <td>
                                            <input type="number" step="0.01" min="0" name="rules[0][min_weight]" class="form-control" placeholder="0" required>
                                        </td>
                                        <td>
                                            <input type="number" step="0.01" min="0" name="rules[0][max_weight]" class="form-control" placeholder="2" required>
                                        </td>
                                        <td>
                                            <input type="number" step="0.01" min="0" name="rules[0][delivery_charge]" class="form-control" placeholder="50" required>
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-sm btn-outline-danger remove-row">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="col-12 d-flex justify-content-end mt-4">
                            <button type="submit" class="btn btn-primary py-2 px-5">
                                {{ __('Submit') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <script>
        let weightRangeIndex = 1;

        function reindexWeightRows() {
            const rows = document.querySelectorAll('.weight-range-row');
            rows.forEach((row, index) => {
                row.querySelectorAll('input').forEach((input) => {
                    const field = input.name.replace(/^rules\[\d+\]\[/, '').replace(/\]$/, '');
                    input.name = `rules[${index}][${field}]`;
                });
            });
            weightRangeIndex = rows.length;
        }

        function addWeightRangeRow() {
            const tbody = document.getElementById('weightRangeBody');
            if (!tbody) return;

            const row = document.createElement('tr');
            row.className = 'weight-range-row';
            row.innerHTML = `
                <td>
                    <input type="number" step="0.01" min="0" name="rules[${weightRangeIndex}][min_weight]" class="form-control" placeholder="0" required>
                </td>
                <td>
                    <input type="number" step="0.01" min="0" name="rules[${weightRangeIndex}][max_weight]" class="form-control" placeholder="5" required>
                </td>
                <td>
                    <input type="number" step="0.01" min="0" name="rules[${weightRangeIndex}][delivery_charge]" class="form-control" placeholder="80" required>
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-outline-danger remove-row" aria-label="Delete range" onclick="removeWeightRangeRow(this.closest('tr'))">
                        <i class="fa fa-trash"></i>
                    </button>
                </td>
            `;

            tbody.appendChild(row);
            const firstInput = row.querySelector('input');
            if (firstInput) firstInput.focus();
            weightRangeIndex += 1;
        }

        function removeWeightRangeRow(row) {
            if (!row) return;
            const rows = document.querySelectorAll('.weight-range-row');
            if (rows.length <= 1) return;
            row.remove();
            reindexWeightRows();
        }
    </script>
@endsection
