<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\MetaPixelEvent;
use App\Models\Product;
use App\Services\MetaConversionsApi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class MetaPixelController extends Controller
{
    /**
     * Mirror a browser pixel event to the Meta Conversions API.
     *
     * The storefront fires `fbq('track', ...)` and calls this endpoint with the
     * same `event_id`, so Meta deduplicates the pair and we still get the event
     * when the browser call is blocked.
     */
    public function track(Request $request, MetaConversionsApi $capi)
    {
        $this->stringifyProductIds($request);

        $data = $request->validate([
            'event_name' => ['required', 'string', Rule::in(MetaConversionsApi::ALLOWED_EVENTS)],
            'event_id' => ['required', 'string', 'max:100'],
            'event_source_url' => ['nullable', 'string', 'max:2048'],
            // Every custom_data key must be listed: as soon as one `custom_data.*`
            // rule exists, Laravel's validated() returns only the keys named here.
            'custom_data' => ['nullable', 'array'],
            'custom_data.currency' => ['nullable', 'string', 'max:8'],
            'custom_data.value' => ['nullable', 'numeric'],
            'custom_data.content_type' => ['nullable', 'string', 'max:32'],
            'custom_data.content_name' => ['nullable', 'string', 'max:255'],
            'custom_data.content_category' => ['nullable', 'string', 'max:255'],
            'custom_data.num_items' => ['nullable', 'integer'],
            'custom_data.content_ids' => ['nullable', 'array', 'max:200'],
            'custom_data.content_ids.*' => ['nullable', 'string', 'max:64'],
            'custom_data.contents' => ['nullable', 'array', 'max:200'],
            'custom_data.contents.*.id' => ['nullable', 'string', 'max:64'],
            'custom_data.contents.*.quantity' => ['nullable', 'integer'],
            'custom_data.contents.*.item_price' => ['nullable', 'numeric'],
            'email' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:32'],
            'first_name' => ['nullable', 'string', 'max:100'],
            'last_name' => ['nullable', 'string', 'max:100'],
            'fbclid' => ['nullable', 'string', 'max:255'],
        ]);

        $sent = $capi->send(
            $data['event_name'],
            $data['event_id'],
            $data['custom_data'] ?? [],
            $request
        );

        if (in_array($data['event_name'], MetaPixelEvent::LOGGED_EVENTS, true)) {
            $this->logEvent($data, $request);
        }

        return response()->json(['sent' => $sent]);
    }

    /**
     * Persist a row for the admin panel's tracking log. Best-effort: a logging
     * failure must never surface to the storefront or break the CAPI response.
     */
    private function logEvent(array $data, Request $request): void
    {
        try {
            $custom = $data['custom_data'] ?? [];
            $productIds = array_values(array_filter(array_map('strval', $custom['content_ids'] ?? [])));

            $products = $productIds
                ? Product::whereIn('id', $productIds)->with('shop:id,name')->get(['id', 'name', 'shop_id'])
                : collect();

            $shopIds = $products->pluck('shop_id')->filter()->unique();
            $shop = $shopIds->count() === 1 ? $products->first()?->shop : null;

            $user = $request->user() ?? auth('sanctum')->user();
            $guestName = trim(($request->input('first_name') ?? '').' '.($request->input('last_name') ?? ''));
            $productName = $custom['content_name'] ?? $products->pluck('name')->implode(', ');

            MetaPixelEvent::create([
                'event_name' => $data['event_name'],
                'event_id' => $data['event_id'],
                'user_id' => $user?->id,
                'guest_name' => $user ? null : ($guestName ?: null),
                'guest_email' => $user ? null : $request->input('email'),
                'page_url' => $data['event_source_url'] ?? $request->headers->get('referer'),
                'product_ids' => $productIds ?: null,
                'product_name' => $productName ? Str::limit($productName, 250, '') : null,
                'shop_id' => $shop?->id,
                'shop_name' => $shop?->name,
                'value' => $custom['value'] ?? null,
                'currency' => $custom['currency'] ?? null,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('Meta pixel event log failed', ['message' => $e->getMessage()]);
        }
    }

    /**
     * Meta treats product ids as strings. Callers naturally send the numeric
     * primary key, so coerce before validating rather than rejecting the event.
     */
    private function stringifyProductIds(Request $request): void
    {
        $custom = $request->input('custom_data');

        if (! is_array($custom)) {
            return;
        }

        if (is_array($custom['content_ids'] ?? null)) {
            $custom['content_ids'] = array_map(
                fn ($id) => is_scalar($id) ? (string) $id : $id,
                $custom['content_ids']
            );
        }

        if (is_array($custom['contents'] ?? null)) {
            $custom['contents'] = array_map(function ($item) {
                if (is_array($item) && isset($item['id']) && is_scalar($item['id'])) {
                    $item['id'] = (string) $item['id'];
                }

                return $item;
            }, $custom['contents']);
        }

        $request->merge(['custom_data' => $custom]);
    }
}
