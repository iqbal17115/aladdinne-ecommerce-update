<?php

namespace Modules\PreOrder\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\PreOrder\App\Http\Requests\PreOrderSettingRequest;
use Modules\PreOrder\App\Models\PreOrderSetting;
use Modules\PreOrder\App\Repositories\PreOrderSettingRepository;

class PreOrderSettingController extends Controller
{

    public function index()
    {
        $data['preOrderSetting'] = PreOrderSettingRepository::first();
        $this->authorize('onlyAdminShop', PreOrderSetting::class);
        return view('preorder::setting', $data);
    }
    public function updateCreate(PreOrderSettingRequest $request)
    {
        $this->authorize('onlyAdminShop', PreOrderSetting::class);
        PreOrderSettingRepository::storeByRequestFrom($request);
        return redirect()->back()->with('success', __('PreOrder Setting Updated Successfully'));
    }
}
