<?php

namespace App\Http\Controllers\API;

use App\Models\Country;
use App\Http\Controllers\Controller;
use App\Http\Resources\AreaResource;
use App\Repositories\AreaRepository;
use App\Http\Resources\ThanaResource;
use App\Repositories\ThanaRepository;
use Illuminate\Support\Facades\Cache;
use App\Http\Resources\CountryResource;

class CountryController extends Controller
{
    public function index()
    {
        $countries = Cache::rememberForever('countries', function () {
            return Country::all();
        });

        return $this->json('all countries', [
            'countries' => CountryResource::collection($countries),
        ]);
    }
    public function indexAreas()
    {
        $areas =  AreaRepository::query()->orderBy('name', 'asc')->isActive()->get();

        return $this->json('all areas', [
            'areas' => AreaResource::collection($areas),
        ]);
    }

    public function indexThanas()
    {
        $areaId = request('area_id');

        $thanas = ThanaRepository::query()
            ->when($areaId, function ($query, $areaId) {
                $query->where('area_id', $areaId);
            })
            ->orderBy('name', 'asc')->isActive()->get();

        return $this->json('all thanas', [
            'thanas' => ThanaResource::collection($thanas),
        ]);
    }

}
