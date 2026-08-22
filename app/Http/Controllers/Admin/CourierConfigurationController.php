<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class CourierConfigurationController extends Controller
{
    /**
     * Display the courier configuration form.
     */
    public function index()
    {
        return view('admin.courier-config');
    }

    /**
     * Update the SteadFast courier configuration.
     */
    public function update(Request $request)
    {
        $request->validate([
            'api_key' => 'required|string',
            'secret_key' => 'required|string',
            'base_url' => 'required|url',
            'default_delivery_type' => 'required|in:0,1',
        ]);

        try {
            $this->setEnv('STEADFAST_API_KEY', $request->api_key);
            $this->setEnv('STEADFAST_SECRET_KEY', $request->secret_key);
            $this->setEnv('STEADFAST_BASE_URL', $request->base_url);
            $this->setEnv('STEADFAST_DEFAULT_DELIVERY_TYPE', $request->default_delivery_type);
            $this->setEnv('STEADFAST_ACTIVE', $request->boolean('is_active') ? 'true' : 'false');

            Artisan::call('config:clear');
            Artisan::call('cache:clear');

            return back()->with('success', __('Courier configuration updated successfully.'));
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
