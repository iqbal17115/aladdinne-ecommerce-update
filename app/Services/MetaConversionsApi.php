<?php

namespace App\Services;

use App\Models\GeneraleSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Sends server-side events to the Meta Conversions API.
 *
 * Every event the browser pixel fires is mirrored here with the same event_id
 * so Meta can deduplicate the pair and keep reporting accurate even when the
 * browser call is blocked (ad blockers, iOS ATT, network failures).
 *
 * @see https://developers.facebook.com/docs/marketing-api/conversions-api
 */
class MetaConversionsApi
{
    /**
     * Events we accept from the storefront. Anything else is dropped so a
     * tampered request cannot pollute the ad account with arbitrary events.
     */
    public const ALLOWED_EVENTS = [
        'PageView',
        'ViewContent',
        'AddToCart',
        'InitiateCheckout',
        'Purchase',
    ];

    protected ?GeneraleSetting $setting;

    public function __construct()
    {
        $this->setting = GeneraleSetting::first();
    }

    /**
     * The pixel/dataset id, from the admin panel first then the env fallback.
     */
    public function pixelId(): ?string
    {
        return $this->setting?->meta_pixel_id ?: config('services.meta.pixel_id');
    }

    public function accessToken(): ?string
    {
        return $this->setting?->meta_capi_access_token ?: config('services.meta.capi_access_token');
    }

    public function testEventCode(): ?string
    {
        return $this->setting?->meta_test_event_code ?: config('services.meta.test_event_code');
    }

    /**
     * CAPI only runs when tracking is switched on and both credentials exist.
     */
    public function enabled(): bool
    {
        return (bool) $this->setting?->meta_pixel_enabled
            && filled($this->pixelId())
            && filled($this->accessToken());
    }

    /**
     * Send a single event. Never throws — tracking must not break checkout.
     *
     * @param  string  $eventName  One of self::ALLOWED_EVENTS
     * @param  string  $eventId    Shared with the browser pixel for deduplication
     * @param  array   $customData value / currency / contents / content_ids ...
     */
    public function send(string $eventName, string $eventId, array $customData, Request $request): bool
    {
        if (! $this->enabled() || ! in_array($eventName, self::ALLOWED_EVENTS, true)) {
            return false;
        }

        $event = array_filter([
            'event_name' => $eventName,
            'event_time' => now()->timestamp,
            'event_id' => $eventId,
            'event_source_url' => $request->input('event_source_url') ?: $request->headers->get('referer'),
            'action_source' => 'website',
            'user_data' => $this->userData($request),
            'custom_data' => $this->customData($customData),
        ], fn ($value) => $value !== null && $value !== []);

        $payload = array_filter([
            'data' => [$event],
            'test_event_code' => $this->testEventCode() ?: null,
        ]);

        try {
            $response = Http::timeout(5)
                ->asJson()
                ->post($this->endpoint(), $payload + ['access_token' => $this->accessToken()]);

            if ($response->failed()) {
                Log::warning('Meta CAPI event rejected', [
                    'event' => $eventName,
                    'status' => $response->status(),
                    'body' => $response->json() ?? $response->body(),
                ]);

                return false;
            }

            return true;
        } catch (Throwable $e) {
            Log::warning('Meta CAPI request failed', [
                'event' => $eventName,
                'message' => $e->getMessage(),
            ]);

            return false;
        }
    }

    protected function endpoint(): string
    {
        $version = config('services.meta.graph_version', 'v21.0');

        return "https://graph.facebook.com/{$version}/{$this->pixelId()}/events";
    }

    /**
     * Build the user_data block. Personally identifying fields must be sent as
     * lowercase, trimmed SHA-256 hashes; fbp/fbc/ip/user-agent are sent raw.
     */
    protected function userData(Request $request): array
    {
        // The endpoint is public so guests can be tracked too; resolve the token
        // manually instead of forcing the request through auth middleware.
        $user = $request->user() ?? auth('sanctum')->user();

        $data = [
            'client_ip_address' => $request->ip(),
            'client_user_agent' => $request->userAgent(),
            'fbp' => $request->cookie('_fbp'),
            'fbc' => $request->cookie('_fbc') ?: $this->fbcFromClickId($request),
        ];

        if ($user) {
            $data['em'] = $this->hash($user->email);
            $data['ph'] = $this->hash($this->normalizePhone($user->phone, $user->phone_code));
            $data['fn'] = $this->hash($user->name);
            $data['ln'] = $this->hash($user->last_name);
            $data['external_id'] = $this->hash((string) $user->id);
        } else {
            // Guest checkout posts the details it collected on the form.
            $data['em'] = $this->hash($request->input('email'));
            $data['ph'] = $this->hash($this->normalizePhone($request->input('phone'), $request->input('phone_code')));
            $data['fn'] = $this->hash($request->input('first_name'));
            $data['ln'] = $this->hash($request->input('last_name'));
        }

        return array_filter($data, fn ($value) => filled($value));
    }

    /**
     * Rebuild the click id cookie value when the browser never stored it but
     * the landing URL still carried ?fbclid=.
     */
    protected function fbcFromClickId(Request $request): ?string
    {
        $fbclid = $request->input('fbclid');

        return filled($fbclid) ? 'fb.1.'.now()->getTimestampMs().'.'.$fbclid : null;
    }

    /**
     * Keep only the keys Meta understands and coerce them into the right shape.
     */
    protected function customData(array $data): array
    {
        $custom = [
            'currency' => $data['currency'] ?? null,
            'value' => isset($data['value']) ? round((float) $data['value'], 2) : null,
            'content_type' => $data['content_type'] ?? null,
            'content_name' => $data['content_name'] ?? null,
            'content_category' => $data['content_category'] ?? null,
            'num_items' => isset($data['num_items']) ? (int) $data['num_items'] : null,
            'order_id' => isset($data['order_id']) ? (string) $data['order_id'] : null,
            'search_string' => $data['search_string'] ?? null,
        ];

        if (! empty($data['content_ids']) && is_array($data['content_ids'])) {
            $custom['content_ids'] = array_values(array_map('strval', $data['content_ids']));
        }

        if (! empty($data['contents']) && is_array($data['contents'])) {
            $custom['contents'] = array_values(array_map(fn ($item) => array_filter([
                'id' => isset($item['id']) ? (string) $item['id'] : null,
                'quantity' => isset($item['quantity']) ? (int) $item['quantity'] : null,
                'item_price' => isset($item['item_price']) ? round((float) $item['item_price'], 2) : null,
            ], fn ($value) => $value !== null), $data['contents']));
        }

        return array_filter($custom, fn ($value) => $value !== null && $value !== []);
    }

    /**
     * Digits-only E.164-ish phone number, country code included when known.
     */
    protected function normalizePhone(?string $phone, ?string $phoneCode = null): ?string
    {
        if (blank($phone)) {
            return null;
        }

        $phone = preg_replace('/\D/', '', $phone);
        $code = preg_replace('/\D/', '', (string) $phoneCode);

        if (blank($phone)) {
            return null;
        }

        return filled($code) && ! str_starts_with($phone, $code) ? $code.$phone : $phone;
    }

    protected function hash(?string $value): ?string
    {
        if (blank($value)) {
            return null;
        }

        return hash('sha256', mb_strtolower(trim($value)));
    }
}
