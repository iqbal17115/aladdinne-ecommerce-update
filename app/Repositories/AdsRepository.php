<?php

namespace App\Repositories;

use App\Http\Requests\BannerRequest;
use App\Models\Ad;
use Illuminate\Support\Facades\Storage;

class AdsRepository extends Repository
{
    /**
     * base method
     *
     * @method model()
     */
    public static function model()
    {
        return Ad::class;
    }

    /**
     * store new banner
     *
     * */
    public static function storeByRequest(BannerRequest $request): Ad
    {

        return self::create([
            'title' => $request->title,
            'banner' => $request->banner,
            'status' => true,
        ]);
    }

    /**
     * Update the banner.
     */
    public static function updateByRequest($request, Ad $ad): Ad
    {

        $ad->update([
            'title' => $request->title,
            'banner' => $request->banner,
        ]);

        return $ad;
    }

    /**
     * delete banner
     *
     * */
    public static function destroy(Ad $ad): bool
    {
        $media = $ad->media;
        if (Storage::exists($media->src)) {
            Storage::delete($media->src);
        }
        $ad->delete();
        $media->delete();

        return true;
    }
}
