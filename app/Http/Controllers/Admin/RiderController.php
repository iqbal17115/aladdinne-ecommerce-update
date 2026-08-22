<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Roles;
use App\Models\User;
use App\Models\Order;
use App\Models\Driver;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use Illuminate\Http\Request;
use App\Http\Requests\RiderRequest;
use App\Http\Controllers\Controller;
use App\Repositories\UserRepository;
use App\Repositories\DriverRepository;
use App\Http\Resources\DriverLocationResource;

class RiderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $status = request('status');

        $riders = User::role(Roles::DRIVER->value)->when($status, function ($query) use ($status) {
            $status = $status == 'approved' ? true : false;

            return $query->where('is_active', $status);
        })->latest('id')->paginate(20);

        return view('admin.rider.index', compact('riders'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.rider.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(RiderRequest $request)
    {
        $user = UserRepository::storeByRequest($request);
        $user->assignRole(Roles::DRIVER->value);

        DriverRepository::storeByUser($user);

        $user->update(['is_active' => true]);

        return to_route('admin.rider.index')->withSuccess(__('Rider created successfully'));
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        $driver = $user->driver;

        $totalDelivery = $driver->driverOrders()->where('is_completed', true)->count();
        $totalPending = $driver->driverOrders()->where('is_completed', false)->count();

        $allCashCollected = $driver->orders()->where('order_status', OrderStatus::DELIVERED->value)->where('payment_method', PaymentMethod::CASH->value)->sum('payable_amount');

        $wallet = DriverRepository::getWallet($driver);

        $alreadyWithdraw = $driver->user->withdraws()->where('status', 'approved')->sum('amount');

        $pendingWithdraw = $driver->user->withdraws()->where('status', 'pending')->sum('amount');

        $deniedWithdraw = $driver->user->withdraws()->where('status', 'denied')->sum('amount');

        return view('admin.rider.show', compact('user', 'driver', 'totalDelivery', 'totalPending', 'allCashCollected', 'alreadyWithdraw', 'pendingWithdraw', 'deniedWithdraw', 'wallet'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        return view('admin.rider.edit', compact('user'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(RiderRequest $request, User $user)
    {
        UserRepository::updateByRequest($request, $user);

        return to_route('admin.rider.index')->withSuccess(__('Rider updated successfully'));
    }

    /**
     * toggle the status of the specified resource.
     */
    public function statusToggle(User $user)
    {
        $user->update([
            'is_active' => ! $user->is_active,
        ]);

        return back()->withSuccess(__('Rider status updated successfully'));
    }

    /**
     * assign order to rider
     */
    public function assignOrder(Order $order, Request $request)
    {

        if (in_array($order->order_status, [
            OrderStatus::CANCELLED,
            OrderStatus::DELIVERED
        ])) {
            return back()->with('error', __('Rider cannot be assigned.'));
        }

        $driver = Driver::find($request->rider);

        if (! $driver) {
            return back()->withError(__('Rider not found, please try again'));
        }
        if (!$driver->driverLocation()->exists()) {
            return back()->withError(__('Rider location not found, please assign location first'));
        }
        if($order->products()->where('is_digital', true)->exists()) {
            return back()->withError(__('Digital products can not be assigned to riders'));
        }

        DriverRepository::assignOrder($order, $driver);

        return back()->withSuccess(__('Rider assigned successfully'));
    }
    public function riderLocation($id)
    {
        $driver = Driver::find($id);
        if (! $driver && ! $driver->driverLocation) {
            return $this->json('Rider location not found, please try again');
        }

        return $this->json('Location fetched successfully', [
            'location' => DriverLocationResource::make($driver->driverLocation),
        ]);
    }

}
