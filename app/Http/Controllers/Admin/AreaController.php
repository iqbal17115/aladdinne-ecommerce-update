<?php

namespace App\Http\Controllers\Admin;

use App\Models\Area;
use App\Http\Requests\AreaRequest;
use App\Http\Controllers\Controller;
use App\Repositories\AreaRepository;
use Illuminate\Support\Facades\Cache;

class AreaController extends Controller
{
    public function index()
    {
        $search = request('search');

        $areas = AreaRepository::query()->when($search, function ($query, $search) {
            $query->where('name', 'like', '%' . $search . '%');
        })->orderBy('name', 'asc')->paginate(20)->withQueryString();

        return view('admin.area.index', compact('areas'));
    }

    public function create()
    {
        return view('admin.area.create');
    }

    public function edit(Area $area)
    {
        return view('admin.area.edit', compact('area'));
    }

    public function store(AreaRequest $request)
    {
        AreaRepository::storeByRequest($request);
        $this->removeCache();

        return to_route('admin.area.index')->withSuccess(__('Area created successfully'));
    }

    public function update(AreaRequest $request, Area $area)
    {
        AreaRepository::updateByRequest($request, $area);
        $this->removeCache();

        return to_route('admin.area.index')->withSuccess(__('Area updated successfully'));
    }

    public function destroy(Area $area)
    {
        if (!$area->getAddresses->isEmpty()) {
            return back()->withError(__('Area has addresses, cannot delete'));
        }

        AreaRepository::destroyByRequest($area);
        $this->removeCache();

        return to_route('admin.area.index')->withSuccess(__('Area deleted successfully'));
    }

    public function toggle(Area $area)
    {
        $area->update(['is_active' => !$area->is_active]);
        $this->removeCache();
        return back()->withSuccess('Area status updated successfully');
    }

    private function removeCache()
    {
        Cache::forget('areas');
    }
}
