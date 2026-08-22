@php
    $generaleSetting = App\Models\GeneraleSetting::first();

    $title = $generaleSetting?->name ?? config('app.name', 'ReadyEcommerce');
    $favicon = $generaleSetting?->favicon ?? asset('assets/favicon.png');
    $logo = $generaleSetting?->logo ?? asset('assets/logo.png');
    $description = $generaleSetting?->web_footer_description ?? $title;
    $siteUrl = url('/');
    $metaPixelId = $generaleSetting?->meta_pixel_id ?: config('services.meta.pixel_id');
    // Tracking stays off until an admin flips the switch, and off on local unless
    // META_PIXEL_ON_LOCAL is set so the Test Events tool can be used in dev.
    $metaPixelAllowedHere = ! app()->environment('local') || config('services.meta.pixel_on_local');
    $shouldLoadMetaPixel = $metaPixelAllowedHere
        && filled($metaPixelId)
        && (bool) $generaleSetting?->meta_pixel_enabled;
    // Only tells the SPA whether to mirror events to our CAPI endpoint — the token
    // itself never leaves the server.
    $metaCapiEnabled = $shouldLoadMetaPixel
        && filled($generaleSetting?->meta_capi_access_token ?: config('services.meta.capi_access_token'));

    if (! empty($product)) {
        $title = $product->name;
        $description = strip_tags($product->short_description) ?: $title;
        $logo = $product->thumbnail;
        $siteUrl = url()->current();
    }
@endphp
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="base-url" content="{{ $siteUrl }}">
    <meta name="app-url" content="{{ $siteUrl }}">
    <meta name="meta-pixel-id" content="{{ $shouldLoadMetaPixel ? $metaPixelId : '' }}">
    <meta name="meta-capi-enabled" content="{{ $metaCapiEnabled ? '1' : '0' }}">

    <!-- SEO -->
    <meta name="description" content="{{ $description }}">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="{{ $siteUrl }}">

    <!-- Open Graph -->
    <meta property="og:type" content="{{ ! empty($product) ? 'product' : 'website' }}">
    <meta property="og:site_name" content="{{ $title }}">
    <meta property="og:title" content="{{ $title }}">
    <meta property="og:description" content="{{ $description }}">
    <meta property="og:image" content="{{ $logo }}">
    <meta property="og:url" content="{{ $siteUrl }}">

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $title }}">
    <meta name="twitter:description" content="{{ $description }}">
    <meta name="twitter:image" content="{{ $logo }}">

    <title>{{ $title }}</title>
    <link rel="shortcut icon" href="{{ $favicon }}" type="image/x-icon">

    @if ($shouldLoadMetaPixel)
        <script>
            !function(f, b, e, v, n, t, s) {
                if (f.fbq) return;
                n = f.fbq = function() {
                    n.callMethod ? n.callMethod.apply(n, arguments) : n.queue.push(arguments);
                };
                if (!f._fbq) f._fbq = n;
                n.push = n;
                n.loaded = true;
                n.version = '2.0';
                n.queue = [];
                t = b.createElement(e);
                t.async = true;
                t.src = v;
                s = b.getElementsByTagName(e)[0];
                s.parentNode.insertBefore(t, s);
            }(window, document, 'script', 'https://connect.facebook.net/en_US/fbevents.js');

            window.fbq('init', '{{ $metaPixelId }}');
            window.fbq('track', 'PageView');
        </script>
    @endif

    @vite('resources/css/app.css')
</head>

<body>
    @if ($shouldLoadMetaPixel)
        <noscript>
            <img height="1" width="1" style="display:none"
                src="https://www.facebook.com/tr?id={{ $metaPixelId }}&ev=PageView&noscript=1" alt="meta-pixel" />
        </noscript>
    @endif
    <div id="app"></div>

    @vite('resources/js/app.js')
</body>

</html>
