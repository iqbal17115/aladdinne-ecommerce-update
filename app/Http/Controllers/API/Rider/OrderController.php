<?php

namespace App\Http\Controllers\API\Rider;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\RiderOrderDetailsResource;
use App\Http\Resources\RiderOrderResource;
use App\Repositories\DriverOrderRepository;
use App\Repositories\OrderRepository;
use Carbon\Carbon;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    // public function index(Request $request)
    // {
    //     $driver = auth()->user()->driver;

    //     $page = $request->page ?? 1;
    //     $perPage = $request->per_page ?? 15;
    //     $skip = ($page * $perPage) - $perPage;

    //     $driverOrdersObj = $driver->driverOrders();

    //     $driverOrders = (clone $driverOrdersObj)->where('is_completed', false)->with('order')->orderBy('id', 'DESC');

    //     $total = $driverOrders->count();

    //     $toDoOrder = (clone $driverOrdersObj)->where('is_completed', false)->count();
    //     $completedOrder = (clone $driverOrdersObj)->where('is_completed', true)->count();

    //     return $this->json('dashboard order list', [
    //         'to_do_order' => $toDoOrder,
    //         'completed_order' => $completedOrder,
    //         'total' => $total,
    //         'orders' => RiderOrderResource::collection($driverOrders->skip($skip)->take($perPage)->get()),
    //     ]);
    // }

    public function index(Request $request)
    {
        $driver = auth()->user()->driver;
        $perPage = $request->input('per_page', 15);

        $ordersQuery = $driver->driverOrders()
            ->where('is_completed', false)
            // ->with(['order.customer.user', 'order.shop.user', 'order.address']);
            ->with([
                'order' => function ($query) {
                    $query->select('id', 'customer_id', 'shop_id', 'order_code', 'prefix', 'payable_amount', 'order_status', 'payment_status', 'payment_method', 'address_id');
                },
                'order.customer' => function ($query) {
                    $query->select('id', 'user_id');
                },
                'order.customer.user' => function ($query) {
                    $query->select('id', 'name', 'phone');
                },
                'order.shop' => function ($query) {
                    $query->select('id', 'user_id', 'name', 'address', 'latitude', 'longitude');
                },
                'order.shop.user' => function ($query) {
                    $query->select('id', 'phone');
                },
                'order.address'
            ]);
        $preOrdersQuery = null;
        if (module_exists('PreOrder') && method_exists($driver, 'driverPreOrders')) {
            $preOrdersQuery = $driver->driverPreOrders()
                ->where('is_completed', false)
                // ->with(['preOrder.customer.user', 'preOrder.shop.user', 'preOrder.address']);
                ->with([
                    'preOrder' => function ($query) {
                        $query->select('id', 'customer_id', 'shop_id', 'order_code', 'payable_amount', 'order_status', 'payment_status', 'payment_method', 'address_id');
                    },
                    'preOrder.customer' => function ($query) {
                        $query->select('id', 'user_id');
                    },
                    'preOrder.customer.user' => function ($query) {
                        $query->select('id', 'name', 'phone');
                    },
                    'preOrder.shop' => function ($query) {
                        $query->select('id', 'user_id', 'name', 'address', 'latitude', 'longitude');
                    },
                    'preOrder.shop.user' => function ($query) {
                        $query->select('id', 'phone');
                    },
                    'preOrder.address'
                ]);
        }
        if ($preOrdersQuery) {
            $mergedOrders = $ordersQuery->get()->concat($preOrdersQuery->get())
                ->sortByDesc('created_at');
        } else {
            $mergedOrders = $ordersQuery->latest('id')->get();
        }
        $total = $mergedOrders->count();
        $page = $request->page ?? 1;
        $paginatedItems = $mergedOrders->forPage($page, $perPage)->values();

        // dd($paginatedItems);

        $toDoOrder = (clone $mergedOrders)->where('is_completed', false)->count();;
        $completedOrder = $driver->driverOrders()->where('is_completed', true)->count();

        if (module_exists('PreOrder')) {
            $completedOrder += $driver->driverPreOrders()->where('is_completed', true)->count();
        }

        return $this->json('dashboard order list', [
            'to_do_order' => $toDoOrder,
            'completed_order' => $completedOrder,
            'total' => $total,
            'orders' => RiderOrderResource::collection($paginatedItems),
        ]);
    }


    // show order details
    public function show(Request $request)
    {
        $request->validate([
            'order_id'   => 'required|integer',
            'order_code' => 'required|string',
        ]);

        $orderId = $request->order_id;
        $orderCode = ltrim($request->order_code, '#');

        $relations = [
            'customer.user',
            'shop.user',
            'address'
        ];

        $order = null;
        $type = 'order';
        if (module_exists('PreOrder')) {
            $type = 'preOrder';
            $order = \Modules\PreOrder\App\Repositories\PreOrderRepository::query()
                ->with($relations)
                ->where('id', $orderId)
                ->where('order_code', $orderCode)
                ->first();
        }
        if (!$order) {
            $type = 'order';
            $order = OrderRepository::query()
                ->with($relations)
                ->where('id', $orderId)
                ->where(function ($query) use ($orderCode) {
                    $query->orWhereRaw("CONCAT(COALESCE(prefix, ''), order_code) = ?", [$orderCode]);
                })
                ->first();
        }

        if (!$order) {
            return $this->json('Sorry, this order was not found', [], 422);
        }

        $order->type = $type;

        return $this->json('Order details', [
            'order' => RiderOrderDetailsResource::make($order),
        ]);
    }

    private function getNextStatus(string $currentStatus, bool $isPreOrder = false): string
    {
        if ($isPreOrder) {
            if (!module_exists('PreOrder')) {
                throw new \RuntimeException('Pre-order module is not available');
            }
            return match ($currentStatus) {
                \Modules\PreOrder\App\Enums\PreOrderStatus::IN_SHIPPING->value  => \Modules\PreOrder\App\Enums\PreOrderStatus::PICKUP->value,
                \Modules\PreOrder\App\Enums\PreOrderStatus::PICKUP->value       => \Modules\PreOrder\App\Enums\PreOrderStatus::ON_THE_WAY->value,
                \Modules\PreOrder\App\Enums\PreOrderStatus::ON_THE_WAY->value   => \Modules\PreOrder\App\Enums\PreOrderStatus::DELIVERED->value,
                \Modules\PreOrder\App\Enums\PreOrderStatus::DELIVERED->value    => \Modules\PreOrder\App\Enums\PreOrderStatus::DELIVERED->value,
                default => \Modules\PreOrder\App\Enums\PreOrderStatus::IN_SHIPPING->value,
            };
        }

        return match ($currentStatus) {
            OrderStatus::CONFIRM->value     => OrderStatus::PROCESSING->value,
            OrderStatus::PROCESSING->value  => OrderStatus::PICKUP->value,
            OrderStatus::PICKUP->value      => OrderStatus::ON_THE_WAY->value,
            OrderStatus::ON_THE_WAY->value  => OrderStatus::DELIVERED->value,
            OrderStatus::DELIVERED->value   => 'deliveredAndPaid',
            default                         => OrderStatus::CONFIRM->value,
        };
    }

    public function statusUpdate(Request $request)
    {
        $validated = $request->validate([
            'order_id'     => 'required|integer',
            'order_code'   => 'required|string',
            'order_status' => 'nullable|string|max:255',
        ]);

        $orderId   = $validated['order_id'];
        $orderCode = ltrim($validated['order_code'], '#');

        $relations = ['customer.user', 'shop.user', 'address'];

        $order      = null;
        $driverOrder = null;
        $isPreOrder = false;
        $type       = 'order';

        if (module_exists('PreOrder')) {
            $order = \Modules\PreOrder\App\Repositories\PreOrderRepository::query()
                ->with($relations)
                ->where('id', $orderId)
                ->where('order_code', $orderCode)
                ->first();

            if ($order) {
                $isPreOrder  = true;
                $type        = 'preOrder';
                $driverOrder = \Modules\PreOrder\App\Models\DriverPreOrder::query()
                    ->where('pre_order_id', $orderId)
                    ->first();
            }
        }

        //Fallback to normal Order
        if (!$order) {
            $order = OrderRepository::query()
                ->with($relations)
                ->where('id', $orderId)
                ->where(function ($q) use ($orderCode) {
                    $q->orWhereRaw("CONCAT(COALESCE(prefix, ''), order_code) = ?", [$orderCode]);
                })
                ->first();

            if ($order) {
                $driverOrder = DriverOrderRepository::query()
                    ->where('order_id', $orderId)
                    ->first();
            }
        }

        if (!$order) {
            return $this->json('Order not found', [], 422);
        }

        if (!$driverOrder) {
            return $this->json('No driver assignment found for this order', [], 422);
        }

        if ($driverOrder->is_completed) {
            return $this->json('This order is already completed', [], 422);
        }

        $nextStatus = $this->getNextStatus(
            $order->order_status->value,
            $isPreOrder
        );

        if ($isPreOrder) {
            \Modules\PreOrder\App\Repositories\PreOrderRepository::PreOrderStatusUpdateFromRider(
                $order,
                $driverOrder,
                $nextStatus
            );
        } else {
            OrderRepository::OrderStatusUpdateFromRider(
                $order,
                $driverOrder,
                $nextStatus
            );
        }
        $order->type=$type;

        return $this->json('Order status updated successfully', [
            'order' => RiderOrderDetailsResource::make($order),
        ]);
    }


    // status update
    // public function statusUpdate(Request $request)
    // {
    //     $request->validate([
    //         'order_id'   => 'required|integer',
    //         'order_code' => 'required|string',
    //         'order_status' => 'nullable|string|max:255'
    //     ]);

    //     $orderId = $request->order_id;
    //     $orderCode = ltrim($request->order_code, '#');

    //     $relations = [
    //         'customer.user',
    //         'shop.user',
    //         'address'
    //     ];

    //     $order = null;
    //     $driverOrder = null;
    //     $type = 'order';
    //     if (module_exists('PreOrder')) {
    //         $type = 'preOrder';
    //         $order = \Modules\PreOrder\App\Repositories\PreOrderRepository::query()
    //             ->with($relations)
    //             ->where('id', $orderId)
    //             ->where('order_code', $orderCode)
    //             ->first();
    //         $driverOrder = \Modules\PreOrder\App\Models\DriverPreOrder::query()->where('order_id', $orderId)->first();
    //     }
    //     if (!$order) {
    //         $type = 'order';
    //         $order = OrderRepository::query()
    //             ->with($relations)
    //             ->where('id', $orderId)
    //             ->where(function ($query) use ($orderCode) {
    //                 $query->orWhereRaw("CONCAT(COALESCE(prefix, ''), order_code) = ?", [$orderCode]);
    //             })
    //             ->first();
    //         $driverOrder = DriverOrderRepository::query()->where('order_id', $orderId)->first();
    //     }

    //     if (!$order) {
    //         return $this->json('Sorry, this order was not found', [], 422);
    //     }

    //     if (! $driverOrder) {
    //         return $this->json('Sorry, this order is not found', [], 422);
    //     } elseif ($driverOrder->is_completed) {
    //         return $this->json('Sorry, this order is already delivered', [], 422);
    //     }

    //     $orderStatus = null;

    //     switch ($order->order_status->value) {
    //         case OrderStatus::CONFIRM->value:
    //             $orderStatus = OrderStatus::PROCESSING->value;
    //             break;

    //         case OrderStatus::PROCESSING->value:
    //             $orderStatus = OrderStatus::PICKUP->value;
    //             break;

    //         case OrderStatus::PICKUP->value:
    //             $orderStatus = OrderStatus::ON_THE_WAY->value;
    //             break;

    //         case OrderStatus::ON_THE_WAY->value:
    //             $orderStatus = OrderStatus::DELIVERED->value;
    //             break;

    //         case OrderStatus::DELIVERED->value:
    //             $orderStatus = 'deliveredAndPaid';
    //             break;

    //         default:
    //             $orderStatus = OrderStatus::CONFIRM->value;
    //             break;
    //     }

    //     OrderRepository::OrderStatusUpdateFromRider($order, $driverOrder, $orderStatus);

    //     // OrderMailEvent::dispatch($order);

    //     return $this->json('Order status updated successfully!', [
    //         'order' => RiderOrderDetailsResource::make($driverOrder->order),
    //     ]);
    // }

    // public function statusWiseOrders(Request $request)
    // {
    //     $page = $request->page ?? 1;
    //     $perPage = $request->per_page ?? 15;
    //     $skip = ($page * $perPage) - $perPage;

    //     $search = $request->search;

    //     $string = $search;

    //     // remove # and 2 letters from search
    //     if (preg_match('/\d/', $string) && ! preg_match('/\s/', $string) && strpos($string, '#') !== false) {
    //         $search = substr($string, 3);
    //     }

    //     $startDate = $request->start_date ? Carbon::parse($request->start_date)->format('Y-m-d') : null;
    //     $endDate = $request->end_date ? Carbon::parse($request->end_date)->format('Y-m-d') : null;

    //     $filterType = $request->filter_type ?? null;

    //     $orderStatus = $request->order_status ?? null;

    //     $driver = auth()->user()->driver;

    //     $driverOrdersObj = $driver->driverOrders();

    //     $driverOrders = (clone $driverOrdersObj)->when($search, function ($query) use ($search) {
    //         return $query->whereHas('order', function ($query) use ($search) {
    //             return $query->where('order_code', 'like', "%$search%")->orWhereHas('customer', function ($query) use ($search) {
    //                 $query->whereHas('user', function ($query) use ($search) {
    //                     return $query->where('name', 'like', "%$search%");
    //                 });
    //             });
    //         });
    //     })->when($startDate, function ($query) use ($startDate, $endDate) {
    //         return $query->where(function ($query) use ($startDate, $endDate) {
    //             $query->whereBetween('created_at', [$startDate, $endDate])->orWhereBetween('updated_at', [$startDate, $endDate]);
    //         });
    //     })->when($filterType == 'today', function ($query) {
    //         return $query->where(function ($query) {
    //             $query->whereDate('created_at', Carbon::today())->orWhereDate('updated_at', Carbon::today());
    //         });
    //     })->when($filterType == 'this_week', function ($query) {
    //         return $query->where(function ($query) {
    //             return $query->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])->orWhereBetween('updated_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
    //         });
    //     })->when($filterType == 'this_month', function ($query) {
    //         return $query->where(function ($query) {
    //             $query->whereMonth('created_at', Carbon::now()->month)->orWhereMonth('updated_at', Carbon::now()->month);
    //         });
    //     })->when($filterType == 'this_year', function ($query) {
    //         return $query->where(function ($query) {
    //             $query->whereYear('created_at', Carbon::now()->year)->orWhereYear('updated_at', Carbon::now()->year);
    //         });
    //     })->when($filterType == 'last_week', function ($query) {
    //         return $query->where(function ($query) {
    //             $query->whereBetween('created_at', [Carbon::now()->subWeek(), Carbon::now()->subWeek(1)])->orWhereBetween('updated_at', [Carbon::now()->subWeek(), Carbon::now()->subWeek(1)]);
    //         });
    //     })->when($filterType == 'last_month', function ($query) {
    //         return $query->where(function ($query) {
    //             $query->whereMonth('created_at', Carbon::now()->subMonth()->month)->orWhereMonth('updated_at', Carbon::now()->subMonth()->month);
    //         });
    //     })->when($filterType == 'last_year', function ($query) {
    //         return $query->where(function ($query) {
    //             $query->whereYear('created_at', Carbon::now()->subYear()->year)->orWhereYear('updated_at', Carbon::now()->subYear()->year);
    //         });
    //     })->when($orderStatus == 'to_deliver', function ($query) {
    //         return $query->where('is_completed', false);
    //     })->when($orderStatus == 'delivered', function ($query) {
    //         return $query->where('is_completed', true);
    //     });

    //     $total = $driverOrders->count();

    //     $driverOrders = $driverOrders->skip($skip)->take($perPage)->get();

    //     $totalOrders = $driver->driverOrders->count();
    //     $totalDelivered = (clone $driverOrdersObj)->where('is_completed', true)->count();
    //     $totalToDeliver = (clone $driverOrdersObj)->where('is_completed', false)->count();

    //     return $this->json('all order list', [
    //         'total' => $total,
    //         'all_orders' => $totalOrders,
    //         'to_deliver' => $totalToDeliver,
    //         'delivered' => $totalDelivered,
    //         'orders' => RiderOrderResource::collection($driverOrders),
    //     ]);
    // }

    public function statusWiseOrders(Request $request)
    {
        $driver = auth()->user()->driver;
        $page = $request->page ?? 1;
        $perPage = $request->per_page ?? 15;

        $search = $request->search;
        if ($search && preg_match('/\d/', $search) && !preg_match('/\s/', $search) && str_contains($search, '#')) {
            $search = substr($search, 3);
        }

        $startDate = $request->start_date ? Carbon::parse($request->start_date)->startOfDay() : null;
        $endDate = $request->end_date ? Carbon::parse($request->end_date)->endOfDay() : null;
        $filterType = $request->filter_type;
        $orderStatus = $request->order_status;

        $orderWith = [
            'order' => fn($q) => $q->select('id', 'customer_id', 'shop_id', 'order_code', 'prefix', 'payable_amount', 'order_status', 'payment_status', 'payment_method', 'address_id'),
            'order.customer.user' => fn($q) => $q->select('id', 'name', 'phone'),
            'order.shop.user' => fn($q) => $q->select('id', 'phone'),
            'order.address'
        ];

        $preOrderWith = [
            'preOrder' => fn($q) => $q->select('id', 'customer_id', 'shop_id', 'order_code', 'payable_amount', 'order_status', 'payment_status', 'payment_method', 'address_id'),
            'preOrder.customer.user' => fn($q) => $q->select('id', 'name', 'phone'),
            'preOrder.shop.user' => fn($q) => $q->select('id', 'phone'),
            'preOrder.address'
        ];

        $applyFilters = function ($query, $isPreOrder = false) use ($search, $startDate, $endDate, $filterType, $orderStatus) {
            $relation = $isPreOrder ? 'preOrder' : 'order';

            return $query->when($search, function ($q) use ($search, $relation, $isPreOrder) {
                $q->whereHas($relation, function ($q) use ($search, $isPreOrder) {
                    $q->where(function ($sub) use ($search, $isPreOrder) {
                        $sub->where('order_code', 'like', "%$search%");
                        if (!$isPreOrder) {
                            $sub->orWhereRaw("CONCAT(COALESCE(prefix, ''), order_code) LIKE ?", ["%{$search}%"]);
                        }
                        $sub->orWhereHas('customer.user', function ($userQuery) use ($search) {
                            $userQuery->where('name', 'like', "%$search%")
                                ->orWhere('phone', 'like', "%$search%");
                        });
                    });
                });
            })
                ->when($startDate, fn($q) => $q->whereBetween('created_at', [$startDate, $endDate]))
                ->when($orderStatus == 'to_deliver', fn($q) => $q->where('is_completed', false))
                ->when($orderStatus == 'delivered', fn($q) => $q->where('is_completed', true));
        };

        $ordersQuery = $applyFilters($driver->driverOrders()->with($orderWith));

        $mergedOrders = $ordersQuery->get();

        if (module_exists('PreOrder') && method_exists($driver, 'driverPreOrders')) {
            $preOrdersQuery = $applyFilters($driver->driverPreOrders()->with($preOrderWith), true);
            $mergedOrders = $mergedOrders->concat($preOrdersQuery->get());
        }

        $mergedOrders = $mergedOrders->sortByDesc('created_at')->values();

        $totalDelivered = $driver->driverOrders()->where('is_completed', true)->count() +
            (module_exists('PreOrder') ? $driver->driverPreOrders()->where('is_completed', true)->count() : 0);

        $totalToDeliver = $driver->driverOrders()->where('is_completed', false)->count() +
            (module_exists('PreOrder') ? $driver->driverPreOrders()->where('is_completed', false)->count() : 0);

        $paginatedItems = $mergedOrders->forPage($page, $perPage)->values();

        return $this->json('all order list', [
            'total'        => $mergedOrders->count(),
            'all_orders'   => $totalDelivered + $totalToDeliver,
            'to_deliver'   => $totalToDeliver,
            'delivered'    => $totalDelivered,
            'orders'       => RiderOrderResource::collection($paginatedItems),
        ]);
    }
}
