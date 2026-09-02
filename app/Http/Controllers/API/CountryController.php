<?php

namespace App\Http\Controllers\API;

use App\Models\Area;
use App\Models\Country;
use App\Http\Controllers\Controller;
use App\Http\Resources\AreaResource;
use App\Http\Resources\ThanaResource;
use App\Repositories\AreaRepository;
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

    public function indexThanas(Area $area)
    {
        $thanas = $area->thanas()->isActive()->orderBy('name', 'asc')->get();

        return $this->json('all thanas', [
            'thanas' => ThanaResource::collection($thanas),
        ]);
    }
}
