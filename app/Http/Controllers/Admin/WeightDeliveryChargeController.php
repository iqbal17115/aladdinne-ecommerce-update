<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\WeightDeliveryChargeRequest;
use App\Models\Area;
use App\Models\WeightDeliveryCharge;
use App\Repositories\WeightDeliveryChargeRepository;

class WeightDeliveryChargeController extends Controller
{
    public function index()
    {
        $deliveryCharges = WeightDeliveryChargeRepository::query()
            ->with('area')
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('admin.weight-delivery-charge.index', compact('deliveryCharges'));
    }

    public function create()
    {
        $areas = Area::query()->orderBy('name')->get();

        return view('admin.weight-delivery-charge.create', compact('areas'));
    }

    public function store(WeightDeliveryChargeRequest $request)
    {
        $rules = $request->input('rules', []);

        if (is_array($rules) && ! empty($rules)) {
            WeightDeliveryChargeRepository::storeBulkByRequest($request);
            return redirect()->route('admin.weightWiseDeliveryCharge.index')->withSuccess(__('Weight delivery charges saved successfully'));
        }

        WeightDeliveryChargeRepository::storeByRequest($request);
        return redirect()->route('admin.weightWiseDeliveryCharge.index')->withSuccess(__('Weight delivery charge saved successfully'));
    }

    public function edit(WeightDeliveryCharge $deliveryCharge)
    {
        $areas = Area::query()->orderBy('name')->get();
        $areaCharges = WeightDeliveryCharge::query()
            ->where('area_id', $deliveryCharge->area_id)
            ->orderBy('min_weight')
            ->get();

        if ($areaCharges->isEmpty()) {
            $areaCharges = collect([$deliveryCharge]);
        }

        return view('admin.weight-delivery-charge.edit', compact('deliveryCharge', 'areas', 'areaCharges'));
    }

    public function update(WeightDeliveryChargeRequest $request, WeightDeliveryCharge $deliveryCharge)
    {
        $rules = $request->input('rules', []);

        if (is_array($rules) && ! empty($rules)) {
            $areaId = $request->input('area_id', $deliveryCharge->area_id);
            $keepIds = [];

            foreach ($rules as $rule) {
                $chargeId = $rule['id'] ?? null;
                $payload = [
                    'area_id' => $areaId ?: null,
                    'delivery_charge' => (float) ($rule['delivery_charge'] ?? 0),
                    'min_weight' => (float) ($rule['min_weight'] ?? 0),
                    'max_weight' => (float) ($rule['max_weight'] ?? 0),
                ];

                if ($chargeId) {
                    $charge = WeightDeliveryCharge::find($chargeId);
                    if ($charge) {
                        $charge->update($payload);
                        $keepIds[] = $charge->id;
                        continue;
                    }
                }

                $newCharge = WeightDeliveryCharge::create($payload);
                $keepIds[] = $newCharge->id;
            }

            WeightDeliveryCharge::where('area_id', $areaId)
                ->whereNotIn('id', $keepIds)
                ->delete();

            return redirect()->route('admin.weightWiseDeliveryCharge.index')->withSuccess(__('Area delivery charges updated successfully'));
        }

        WeightDeliveryChargeRepository::updateByRequest($request, $deliveryCharge);
        return redirect()->route('admin.weightWiseDeliveryCharge.index');
    }

    public function destroy(WeightDeliveryCharge $deliveryCharge)
    {
        $deliveryCharge->delete();
        return redirect()->route('admin.weightWiseDeliveryCharge.index');
    }
}
