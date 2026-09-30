@extends('layouts.app')
@section('header-title', __('Edit Delivery Charge'))

@section('content')
    <div class="page-title">
        <div class="d-flex gap-2 align-items-center">
            <i class="fa-solid fa-car"></i> {{ __('Edit Delivery Charge') }}
        </div>
    </div>
    <form action="{{ route('admin.weightWiseDeliveryCharge.update', $deliveryCharge->id) }}" method="POST">
        @csrf
        @method('PUT')
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
                                        <option value="{{ $area->id }}" {{ $deliveryCharge->area_id == $area->id ? 'selected' : '' }}>
                                            {{ $area->name }}
                                        </option>
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
                                    @foreach ($areaCharges as $index => $charge)
                                        <tr class="weight-range-row">
                                            <input type="hidden" name="rules[{{ $index }}][id]" value="{{ $charge->id }}">
                                            <td>
                                                <input type="number" step="0.01" min="0" name="rules[{{ $index }}][min_weight]" class="form-control" value="{{ $charge->min_weight }}" required>
                                            </td>
                                            <td>
                                                <input type="number" step="0.01" min="0" name="rules[{{ $index }}][max_weight]" class="form-control" value="{{ $charge->max_weight }}" required>
                                            </td>
                                            <td>
                                                <input type="number" step="0.01" min="0" name="rules[{{ $index }}][delivery_charge]" class="form-control" value="{{ $charge->delivery_charge }}" required>
                                            </td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-sm btn-outline-danger remove-row" aria-label="Delete range">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
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
        let weightRangeIndex = {{ max($areaCharges->count(), 1) }};

        function reindexWeightRows() {
            const rows = document.querySelectorAll('.weight-range-row');
            rows.forEach((row, index) => {
                row.querySelectorAll('input').forEach((input) => {
                    if (input.name.startsWith('rules[')) {
                        const field = input.name.replace(/^rules\[\d+\]\[/, '').replace(/\]$/, '');
                        input.name = `rules[${index}][${field}]`;
                    }
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
                <input type="hidden" name="rules[${weightRangeIndex}][id]" value="">
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
            const firstInput = row.querySelector('input[type="number"]');
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
