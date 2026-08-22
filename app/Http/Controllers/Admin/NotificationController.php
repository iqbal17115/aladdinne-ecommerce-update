<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\WebNotificationResource;
use App\Models\Notification;
use App\Repositories\NotificationRepository;

class NotificationController extends Controller
{
    // fetch notifications for admin
    public function index()
    {
        $shop = generaleSetting('shop');
        $notifications = NotificationRepository::query()
            ->where(function ($query) use ($shop) {
                $query->where('shop_id', $shop->id)
                    ->orWhereNull('shop_id');
            })
            ->whereNull('user_id')
            ->orderBy('is_read', 'asc')->latest('id')->take(10)->get();

        $total = NotificationRepository::query()->where(function ($query) use ($shop) {
            $query->where('shop_id', $shop->id)
                ->orWhereNull('shop_id');
        })
            ->whereNull('user_id')->whereIsRead(false)->count();

        return $this->json('notifications', [
            'total' => $total >= 10 ? '9+' : $total,
            'notifications' => WebNotificationResource::collection($notifications),
        ]);
    }

    // show all notifications
    public function show()
    {
        $shop = generaleSetting('shop');
        $notifications = NotificationRepository::query()
            ->where(function ($query) use ($shop) {
                $query->where('shop_id', $shop->id)
                    ->orWhereNull('shop_id');
            })
            ->whereNull('user_id')
            ->orderBy('is_read', 'asc')->latest('id')->paginate(20);

        return view('admin.notification', compact('notifications'));
    }

    // mark as read
    public function markAsRead(Notification $notification)
    {
        $notification->update(['is_read' => true]);

        if ($notification->url != null) {
            return redirect()->to($notification->url);
        }

        return back();
    }

    // mark all as read
    public function markAllAsRead()
    {
        $shop = generaleSetting('shop');
        NotificationRepository::query()->where(function ($query) use ($shop) {
                $query->where('shop_id', $shop->id)
                    ->orWhereNull('shop_id');
            })->whereNull('user_id')->update(['is_read' => true]);

        return back()->withSuccess(__('All notifications marked as read!'));
    }

    // destroy notification
    public function destroy(Notification $notification)
    {
        $notification->delete();

        return back()->withSuccess(__('Notification deleted successfully'));
    }
}
