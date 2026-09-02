<?php

namespace App\Traits;

use App\Models\Area;
use App\Models\GeneraleSetting;
use App\Models\Thana;
use App\Repositories\AreaRepository;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;


trait DeliveryPriceTrait
{
    protected static function getDeliverySettings()
    {
        return GeneraleSetting::first();
    }

    /**
     * Base rate is the selected Thana's own shipping charge when one is
     * set; otherwise it falls back to the Area's flat delivery_amount
     * (legacy addresses / areas with no thana picked).
     */
    protected static function calculateDeliveryPrice($distance, $duration, $areaId, $thanaId = null)
    {
        $settings = self::getDeliverySettings();

        if ($settings?->is_delivery_free) {
            return 0;
        }

        $areaData = AreaRepository::query()->where('id', $areaId)->first();
        $thana = $thanaId ? Thana::find($thanaId) : null;
        $baseRate = $thana->shipping_charge ?? $areaData->delivery_amount ?? 0;

        if (! $settings?->is_distance_charge) {
            return round($baseRate, 2);
        }

        $pricePerKm = $areaData->price_per_km ?? 0;
        $pricePerMin = $areaData->price_per_min ?? 0;

        $distanceCost = $distance * $pricePerKm;
        $durationCost = $duration * $pricePerMin;

        return round($baseRate + $distanceCost + $durationCost, 2);
    }

    protected static function resolveDeliveryAreaId(?float $latitude, ?float $longitude, $fallbackAreaId = null): ?int
    {
        if ($fallbackAreaId && Area::whereKey($fallbackAreaId)->exists()) {
            return (int) $fallbackAreaId;
        }

        if ($latitude === null || $longitude === null) {
            return null;
        }

        $areas = Area::isActive()->get();

        foreach ($areas as $area) {
            if (static::coordinateFallsInAreaPolygon($latitude, $longitude, $area->polygon_coordinates)) {
                return (int) $area->id;
            }
        }

        foreach ($areas as $area) {
            if (
                $area->latitude === null ||
                $area->longitude === null ||
                empty($area->distance)
            ) {
                continue;
            }

            $distanceKm = static::orderHaversineKm(
                (float) $area->latitude,
                (float) $area->longitude,
                $latitude,
                $longitude,
            );

            if ($distanceKm <= (float) $area->distance) {
                return (int) $area->id;
            }
        }

        return null;
    }

    protected static function coordinateFallsInAreaPolygon(float $latitude, float $longitude, $polygonCoordinates): bool
    {
        if (blank($polygonCoordinates)) {
            return false;
        }

        $points = is_string($polygonCoordinates)
            ? json_decode($polygonCoordinates, true)
            : $polygonCoordinates;

        if (! is_array($points) || count($points) < 3) {
            return false;
        }

        $inside = false;
        $pointCount = count($points);

        for ($i = 0, $j = $pointCount - 1; $i < $pointCount; $j = $i++) {
            if (
                ! isset($points[$i][0], $points[$i][1], $points[$j][0], $points[$j][1])
            ) {
                continue;
            }

            $latI = (float) $points[$i][0];
            $lngI = (float) $points[$i][1];
            $latJ = (float) $points[$j][0];
            $lngJ = (float) $points[$j][1];

            $intersects = (($lngI > $longitude) !== ($lngJ > $longitude))
                && ($latitude < (($latJ - $latI) * ($longitude - $lngI) / (($lngJ - $lngI) ?: PHP_FLOAT_EPSILON)) + $latI);

            if ($intersects) {
                $inside = ! $inside;
            }
        }

        return $inside;
    }

    protected static function orderDistanceDuration(
        float $originLat,
        float $originLng,
        float $destLat,
        float $destLng,
    ): array {
        $apiKey = config('services.google_maps.key');

        if ($apiKey) {
            try {
                $response = Http::timeout(5)->get(
                    'https://maps.googleapis.com/maps/api/distancematrix/json',
                    [
                        'origins'      => "{$originLat},{$originLng}",
                        'destinations' => "{$destLat},{$destLng}",
                        'units'        => 'metric',
                        'key'          => $apiKey,
                    ]
                );

                $json = $response->json();

                if (
                    ($json['status'] ?? '') === 'OK' &&
                    ($json['rows'][0]['elements'][0]['status'] ?? '') === 'OK'
                ) {
                    $element     = $json['rows'][0]['elements'][0];
                    $distanceKm  = round($element['distance']['value'] / 1000, 3);  // metres → km
                    $durationMin = round($element['duration']['value'] / 60, 1);    // seconds → min
                    return compact('distanceKm', 'durationMin');
                }
            } catch (\Throwable $e) {
                Log::warning('orderPriceTrait::order Google API error: ' . $e->getMessage());
            }
        }

        // ── Fallback: Haversine ────────────────────────────────────
        $distanceKm  = static::orderHaversineKm($originLat, $originLng, $destLat, $destLng);
        $durationMin = static::orderEstimateDuration($distanceKm);
        return compact('distanceKm', 'durationMin');
    }

    protected static function orderHaversineKm(
        float $lat1,
        float $lng1,
        float $lat2,
        float $lng2,
    ): float {
        $earthRadius = 6371.0;

        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1))
            * cos(deg2rad($lat2))
            * sin($dLng / 2) ** 2;

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return round($earthRadius * $c, 3);
    }


    protected static function orderEstimateDuration(float $distanceKm): float
    {
        return $distanceKm > 0 ? round(($distanceKm / 40) * 60, 1) : 0.0;
    }
}
