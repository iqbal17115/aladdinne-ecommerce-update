<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GeneraleSetting;
use Illuminate\Http\Request;

class DeliverySettingController extends Controller
{
    public function index()
    {
        $generaleSetting = GeneraleSetting::first();
        return view('admin.delivery-setting.index', compact('generaleSetting'));
    }

    public function update(Request $request)
    {
        $generaleSetting = GeneraleSetting::first();

        $generaleSetting->update([
            'is_weight_charge' => $request->boolean('is_weight_charge'),
            'is_distance_charge' => $request->boolean('is_distance_charge'),
            'is_delivery_free' => $request->boolean('is_delivery_free'),
        ]);

        return back()->withSuccess(__('Courier settings updated successfully'));
    }
}
