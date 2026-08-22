<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\WeightDeliveryChargeRequest;
use App\Models\WeightDeliveryCharge;
use App\Repositories\WeightDeliveryChargeRepository;

class WeightDeliveryChargeController extends Controller
{
    public function index()
    {
        $deliveryCharges = WeightDeliveryChargeRepository::query()->orderBy('id', "desc")->paginate(10);
        return view("admin.weight-delivery-charge.index", compact("deliveryCharges"));
    }
    public function create()
    {
        return view("admin.weight-delivery-charge.create");
    }
    public function store(WeightDeliveryChargeRequest $request)
    {
        WeightDeliveryChargeRepository::storeByRequest($request);
        return redirect()->route("admin.weightWiseDeliveryCharge.index");
    }
    public function edit(WeightDeliveryCharge $deliveryCharge)
    {
        return view("admin.weight-delivery-charge.edit", compact("deliveryCharge"));
    }
    public function update(WeightDeliveryChargeRequest $request, WeightDeliveryCharge $deliveryCharge)
    {
        WeightDeliveryChargeRepository::updateByRequest($request, $deliveryCharge);
        return redirect()->route("admin.weightWiseDeliveryCharge.index");
    }
    public function destroy(WeightDeliveryCharge $deliveryCharge)
    {
        $deliveryCharge->delete();
        return redirect()->route("admin.weightWiseDeliveryCharge.index");
    }
}
