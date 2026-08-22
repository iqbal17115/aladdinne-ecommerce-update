<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CourierTracking;
use App\Services\SteadFastService;

class CourierTrackingController extends Controller
{
    /**
     * Display a listing of courier trackings.
     */
    public function index()
    {
        $courierTrackings = CourierTracking::with(['order', 'preOrder'])->latest('id')->paginate(20);

        return view('admin.courier-tracking.index', compact('courierTrackings'));
    }

    /**
     * Refresh delivery status from SteadFast.
     */
    public function refreshStatus(CourierTracking $courierTracking)
    {
        $response = (new SteadFastService)->getOrder($courierTracking->consignment_id);

        if (! $response['success']) {
            return back()->with('error', $response['message'] ?? __('Failed to fetch status.'));
        }

        $status = $response['data']['delivery_status'] ?? $courierTracking->status;
        $courierTracking->addStatusHistory($status);
        $courierTracking->update(['status' => $status]);

        return back()->with('success', __('Status refreshed successfully.'));
    }
}
