<?php

namespace App\Http\Controllers\Admin;

use App\Models\Area;
use App\Models\Thana;
use App\Http\Requests\ThanaRequest;
use App\Http\Controllers\Controller;
use App\Repositories\ThanaRepository;

class ThanaController extends Controller
{
    public function index()
    {
        $search = request('search');
        $areaId = request('area_id');

        $thanas = ThanaRepository::query()->with('area')
            ->when($search, function ($query, $search) {
                $query->where('name', 'like', '%' . $search . '%');
            })
            ->when($areaId, function ($query, $areaId) {
                $query->where('area_id', $areaId);
            })
            ->orderBy('name', 'asc')->paginate(20)->withQueryString();

        $areas = Area::orderBy('name', 'asc')->get();

        return view('admin.thana.index', compact('thanas', 'areas', 'areaId'));
    }

    public function create()
    {
        $areas = Area::orderBy('name', 'asc')->get();

        return view('admin.thana.create', compact('areas'));
    }

    public function edit(Thana $thana)
    {
        $areas = Area::orderBy('name', 'asc')->get();

        return view('admin.thana.edit', compact('thana', 'areas'));
    }

    public function store(ThanaRequest $request)
    {
        ThanaRepository::storeByRequest($request);

        return to_route('admin.thana.index')->withSuccess(__('Thana created successfully'));
    }

    public function update(ThanaRequest $request, Thana $thana)
    {
        ThanaRepository::updateByRequest($request, $thana);

        return to_route('admin.thana.index')->withSuccess(__('Thana updated successfully'));
    }

    public function destroy(Thana $thana)
    {
        if (!$thana->addresses->isEmpty()) {
            return back()->withError(__('Thana has addresses, cannot delete'));
        }

        ThanaRepository::destroyByRequest($thana);

        return to_route('admin.thana.index')->withSuccess(__('Thana deleted successfully'));
    }

    public function toggle(Thana $thana)
    {
        $thana->update(['is_active' => !$thana->is_active]);

        return back()->withSuccess('Thana status updated successfully');
    }
}
