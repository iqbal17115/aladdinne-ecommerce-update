<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class GoogleMapConfigController extends Controller
{
    /**
     * Google Maps configuration page
     */
    public function index()
    {
        return view('admin.google-maps-config');
    }

    public function update(Request $request)
    {
        $request->validate([
            'google_maps_key' => 'required',
        ]);

        try {
            $this->setEnv('GOOGLE_MAPS_AI_PKEY', $request->google_maps_key);

            Artisan::call('config:clear');
            Artisan::call('cache:clear');

            return to_route('admin.googleMaps.index')->with('success', __('Google Maps config updated successfully'));
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
