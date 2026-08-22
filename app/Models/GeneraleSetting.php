<?php

namespace App\Models;

use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class GeneraleSetting extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $table = 'generate_settings';

    protected $casts = [
        'meta_pixel_enabled' => 'boolean',
    ];

    public function mediaLogo()
    {
        return $this->belongsTo(Media::class, 'logo_id');
    }

    public function mediaAppLogo()
    {
        return $this->belongsTo(Media::class, 'app_logo_id');
    }

    public function mediaDarkLogo()
    {
        return $this->belongsTo(Media::class, 'dark_logo_id');
    }

    public function mediaFavicon()
    {
        return $this->belongsTo(Media::class, 'favicon_id');
    }

    public function mediaFooterLogo()
    {
        return $this->belongsTo(Media::class, 'footer_logo_id');
    }

    public function mediaFooterQr()
    {
        return $this->belongsTo(Media::class, 'footer_qrcode_id');
    }

    public function mediaFooterPaymentImg()
    {
        return $this->belongsTo(Media::class, 'footer_payment_img_id');
    }

    public function mediaFooterBgImage()
    {
        return $this->belongsTo(Media::class, 'footer_bg_image_id');
    }

    public function logo(): Attribute
    {
        $logo = asset('assets/logo.png');
        if ($this->mediaLogo && Storage::exists($this->mediaLogo->src)) {
            $logo = Storage::url($this->mediaLogo->src);
        }

        return new Attribute(
            get: fn () => $logo,
        );
    }

    public function appLogo(): Attribute
    {
        $logo = asset('assets/favicon.png');
        if ($this->mediaAppLogo && Storage::exists($this->mediaAppLogo->src)) {
            $logo = Storage::url($this->mediaAppLogo->src);
        }

        return new Attribute(
            get: fn () => $logo,
        );
    }

    public function darkLogo(): Attribute
    {
        $logo = asset('assets/logo.png');
        if ($this->mediaDarkLogo && Storage::exists($this->mediaDarkLogo->src)) {
            $logo = Storage::url($this->mediaDarkLogo->src);
        }

        return new Attribute(
            get: fn () => $logo,
        );
    }

    public function favicon(): Attribute
    {
        $favicon = asset('assets/favicon.png');
        if ($this->mediaFavicon && Storage::exists($this->mediaFavicon->src)) {
            $favicon = Storage::url($this->mediaFavicon->src);
        }

        return new Attribute(
            get: fn () => $favicon,
        );
    }

    public function footerLogo(): Attribute
    {
        $logo = asset('assets/logo.png');
        if ($this->mediaFooterLogo && Storage::exists($this->mediaFooterLogo->src)) {
            $logo = Storage::url($this->mediaFooterLogo->src);
        }

        return new Attribute(
            get: fn () => $logo,
        );
    }

    public function footerQr(): Attribute
    {
        $qr = null;
        if ($this->mediaFooterQr && Storage::exists($this->mediaFooterQr->src)) {
            $qr = Storage::url($this->mediaFooterQr->src);
        }

        return new Attribute(
            get: fn () => $qr,
        );
    }

    public function footerPaymentImg(): Attribute
    {
        $img = null;
        if ($this->mediaFooterPaymentImg && Storage::exists($this->mediaFooterPaymentImg->src)) {
            $img = Storage::url($this->mediaFooterPaymentImg->src);
        }

        return new Attribute(
            get: fn () => $img,
        );
    }

    public function footerBgImage(): Attribute
    {
        $img = null;
        if ($this->mediaFooterBgImage && Storage::exists($this->mediaFooterBgImage->src)) {
            $img = Storage::url($this->mediaFooterBgImage->src);
        }

        return new Attribute(
            get: fn () => $img,
        );
    }

    protected static function boot()
    {
        parent::boot();

        static::created(function () {
            Cache::forget('generale_setting');
            Cache::forget('default_currency');
            self::clearOrderCache();
        });

        static::updated(function () {
            Cache::forget('generale_setting');
            Cache::forget('default_currency');
            self::clearOrderCache();
        });

        static::deleted(function () {
            Cache::forget('generale_setting');
            Cache::forget('default_currency');
            self::clearOrderCache();
        });
    }

    protected static function clearOrderCache()
    {
        $cacheKeys = [
            'admin_all_orders',
            'shop_all_orders',
        ];

        foreach (OrderStatus::cases() as $status) {
            $cacheKeys[] = 'admin_status_'.Str::camel($status->value);
            $cacheKeys[] = 'shop_status_'.Str::camel($status->value);
        }

        foreach ($cacheKeys as $key) {
            Cache::forget($key);
        }
    }
}
