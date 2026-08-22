<?php

namespace App\Repositories;

use App\Models\ThemeColor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ThemeColorRepository extends Repository
{
    /**
     * base method
     *
     * @method model()
     */
    public static function model()
    {
        return ThemeColor::class;
    }

    public static function DefaultColorUpdate($request)
    {
        self::query()->update([
            'is_default' => false,
        ]);

        self::changeStyleCSS($request->primary_color, $request->secondary_color);

        return self::query()->where('primary', $request->primary_color)->where('secondary', $request->secondary_color)->update([
            'is_default' => true,
        ]);
    }

    public static function updateColorPalette(Request $request)
    {
        $colorVariants = json_decode($request->generated_color_variants);

        $themeColor = self::query()->where('is_default', true)->first();

        $updatedColors = [];

        foreach ($colorVariants as $key => $value) {
            $updatedColors['variant_' . $key] = $value;

            if ($key == 100) {
                $updatedColors['secondary'] = $value;
            }

            if ($key == 500) {
                $updatedColors['primary'] = $value;
            }
        }

        if (! $themeColor) {
            $themeColor = self::create([
                'primary' => $updatedColors['primary'],
                'secondary' => $updatedColors['secondary'],
                'is_default' => true,
            ]);
        }

        $themeColor->update($updatedColors);

        $request['primary_color'] = $updatedColors['primary'];
        $request['secondary_color'] = $updatedColors['secondary'];

        GeneraleSettingRepository::updateOrCreateThemeColor($request);

        // update style.css
        self::changeStyleCSS($updatedColors['primary'], $updatedColors['secondary']);

        return $updatedColors;
    }

    public static function changeStyleCSS($primary, $secondary)
    {
        //    dd($primary ,$secondary);
        // update style.css
        $file = public_path('assets/css/style.css');

        $str = file_get_contents($file);

        $str = preg_replace('/--theme-color:\s*[^;]*;/', "--theme-color: {$primary};", $str);
        $str = preg_replace('/--theme-hover-bg:\s*[^;]*;/', "--theme-hover-bg: {$secondary};", $str);


        try {
            file_put_contents($file, $str);
        } catch (\Throwable $e) {
            Log::error($e->getMessage());
        }

        // update login.css
        $file = public_path('assets/css/login.css');

        $str = file_get_contents($file);

        $str = preg_replace('/--theme_color:\s*[^;]*;/', "--theme_color: {$primary};", $str);

        try {
            file_put_contents($file, $str);
        } catch (\Throwable $e) {
            Log::error($e->getMessage());
        }


        // update lfm.css
        $file = public_path('vendor/laravel-filemanager/css/lfm.css');

        $str = file_get_contents($file);

        $str = preg_replace('/--theme-color:\s*[^;]*;/', "--theme-color: {$primary};", $str);

        try {
            file_put_contents($file, $str);
        } catch (\Throwable $e) {
            Log::error($e->getMessage());
        }
    }
}
