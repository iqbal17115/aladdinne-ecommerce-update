<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MetaPixelEvent;
use App\Models\Shop;
use Illuminate\Http\Request;

class MetaPixelEventController extends Controller
{
    /**
     * Admin-facing log of Meta Pixel commerce events (ViewContent/AddToCart/
     * InitiateCheckout/Purchase) mirrored via the Conversions API.
     */
    public function index(Request $request)
    {
        $eventName = $request->get('event_name');
        $shopId = $request->get('shop_id');
        $from = $request->get('from');
        $to = $request->get('to');

        $events = MetaPixelEvent::query()
            ->with(['user:id,name,email', 'shop:id,name'])
            ->when($eventName, fn ($query) => $query->where('event_name', $eventName))
            ->when($shopId, fn ($query) => $query->where('shop_id', $shopId))
            ->when($from, fn ($query) => $query->whereDate('created_at', '>=', $from))
            ->when($to, fn ($query) => $query->whereDate('created_at', '<=', $to))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        $eventNames = MetaPixelEvent::LOGGED_EVENTS;
        $shops = Shop::select('id', 'name')->orderBy('name')->get();

        return view('admin.meta-pixel-event.index', compact(
            'events',
            'eventNames',
            'shops',
            'eventName',
            'shopId',
            'from',
            'to'
        ));
    }
}
