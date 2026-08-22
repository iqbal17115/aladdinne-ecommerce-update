@extends('layouts.app')
@section('header-title', __('PreOrder Dashboard'))
@section('content')
    @push('css')
        <link rel="stylesheet" href="{{ asset('modules/PreOrder/css/style.css') }}">
    @endpush
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <main class="p-4 grid grid-cols-6 gap-4">
        <div class="widget_container col-span-6 rounded-3 p-3 bg-white d-flex flex-column gap-3">
            <div class=" grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 ">
                <div class="p-4 rounded-3 bg-black overflow-hidden">
                    <p class="fs-2 text-white fw-semibold mb-4">{{ showCurrency(formatAmount($totalSaleAmount)) }}</p>
                    <div class="d-flex justify-content-between align-items-center">
                        <p class="fs-6 text-white fw-normal mb-0">{{ __('Total Sales All Time') }}</p>
                        <div class="itemBox">
                            <img src="{{ asset('assets/reportImage/wallet.svg') }}" alt="">
                        </div>
                    </div>
                </div>
                <div class="p-4 rounded-3 bg-black overflow-hidden">
                    <p class="fs-2 text-white fw-semibold mb-4">{{ showCurrency(formatAmount($totalPaidAmount)) }}</p>
                    <div class="d-flex justify-content-between align-items-center">
                        <p class="fs-6 text-white fw-normal mb-0">{{ __('Payment Received') }}</p>
                        <div class="itemBox">
                            <img src="{{ asset('assets/reportImage/payment.svg') }}" alt="">
                        </div>
                    </div>
                </div>
                <div class="p-4 rounded-3 bg-black overflow-hidden">
                    <p class="fs-2 text-white fw-semibold mb-4">{{ showCurrency(formatAmount($totalDueAmount)) }}</p>
                    <div class="d-flex justify-content-between align-items-center">
                        <p class="fs-6 text-white fw-normal mb-0">{{ __('Total Due Payment') }}</p>
                        <div class="itemBox">
                            <img src="{{ asset('assets/reportImage/amount.svg') }}" alt="">
                        </div>
                    </div>
                </div>
                <div class="p-4 rounded-3 bg-black overflow-hidden">
                    <p class="fs-2 text-white fw-semibold mb-4">{{ $totalProduct }}</p>
                    <div class="d-flex justify-content-between align-items-center">
                        <p class="fs-6 text-white fw-normal mb-0">{{ __('Total Preorder Product') }}</p>
                        <div class="itemBox">
                            <img src="{{ asset('assets/reportImage/purchase.svg') }}" alt="">
                        </div>
                    </div>
                </div>
            </div>
            <div class=" grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 ">
                <div class="p-3 rounded-3 bg-light-blue overflow-hidden">
                    <p class="fs-4 text-primary fw-semibold mb-3">{{ $requestOrder }}</p>
                    <p class="fs-6 text-secondary fw-normal mb-0">{{ __('Preorder Request') }}</p>
                </div>
                <div class="p-3 rounded-3 bg-light-yellow overflow-hidden">
                    <p class="fs-4 text-theme-color fw-semibold mb-3">{{ $acceptedOrder }}</p>
                    <p class="fs-6 text-secondary fw-normal mb-0">{{ __('Accepted Preorders') }}</p>
                </div>
                <div class="p-3 rounded-3 bg-light-purple overflow-hidden">
                    <p class="fs-4 text-dark-purple fw-semibold mb-3">{{ $preRequestOrder }}</p>
                    <p class="fs-6 text-secondary fw-normal mb-0">{{ __('PrePayment Requests') }}</p>
                </div>
                <div class="p-3 rounded-3 bg-light-sky overflow-hidden">
                    <p class="fs-4 text-dark-sky fw-semibold mb-3">{{ $confirmedPreReqOrder }}</p>
                    <p class="fs-6 text-secondary fw-normal mb-0">{{ __('Confirmed PrePayments') }}</p>
                </div>
                <div class="p-3 rounded-3 bg-light-yellow overflow-hidden">
                    <p class="fs-4 text-dark-yellow fw-semibold mb-3">{{ $deliveredOrder }}</p>
                    <p class="fs-6 text-secondary fw-normal mb-0">{{ __('Delivered Preorders') }}</p>
                </div>
            </div>
        </div>
        <div class="col-span-6 lg:col-span-4 p-3 rounded-3 bg-white d-flex flex-column justify-content-between">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <p class="fs-5 fw-semibold mb-0">{{ __('Pre-order Sale Analytics') }}</p>
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex justify-content-between align-items-center gap-2">
                        <button type="button" class="chart-btn-sm " data-type="daily">
                            <p>{{ __('Daily') }}</p>
                        </button>
                        <button type="button" class="chart-btn-sm seleted" data-type="monthly">
                            <p>{{ __('Monthly') }}</p>
                        </button>
                        <button type="button" class="chart-btn-sm" data-type="yearly">
                            <p>{{ __('Yearly') }}</p>
                        </button>
                    </div>
                </div>
            </div>
            <div>
                <div id="sale-analytics-chart" style="width: 100%; max-height: 600px"></div>
            </div>
        </div>
        <div class="col-span-6 lg:col-span-2 p-3 rounded-3 bg-white">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <p class="fs-5 fw-semibold mb-0">{{ __('Order Status') }}</p>
            </div>
            <div class="d-flex flex-column gap-3">
                <div class="bg-light-gray p-3 rounded-2 d-flex justify-content-between align-content-stretch">
                    <div class="w-100 d-flex flex-column gap-2">
                        <p class="fs-4 text-black fw-semibold m-0">{{ showCurrency(formatAmount($inShippingAmount)) }}</p>
                        <p class="fs-6 m-0 text-secondary">{{ __('In Shipping') }}</p>
                    </div>
                    <div class="d-flex align-items-center">
                        <div class="bg-white p-2 rounded-2">
                            <img src="{{ asset('assets/images/order-status-1.png') }}" alt="">
                        </div>
                    </div>
                </div>
                <div class="bg-light-gray p-3 rounded-2 d-flex justify-content-between align-content-stretch">
                    <div class="w-100 d-flex flex-column gap-2">
                        <p class="fs-4 text-black fw-semibold m-0">{{ showCurrency(formatAmount($deliveredAmount)) }}</p>
                        <p class="fs-6 m-0 text-secondary">{{ __('Delivered') }}</p>
                    </div>
                    <div class="d-flex align-items-center">
                        <div class="bg-white p-2 rounded-2">
                            <img src="{{ asset('assets/images/order-status-2.png') }}" alt="">
                        </div>
                    </div>
                </div>
                <div class="bg-light-gray p-3 rounded-2 d-flex justify-content-between align-content-stretch">
                    <div class="w-100 d-flex flex-column gap-2">
                        <p class="fs-4 text-black fw-semibold m-0">{{ showCurrency(formatAmount($totalPaidAmount)) }}</p>
                        <p class="fs-6 m-0 text-secondary">{{ __('Delayed Pre-payment Orders') }}</p>
                    </div>
                    <div class="d-flex align-items-center">
                        <div class="bg-white p-2 rounded-2">
                            <img src="{{ asset('assets/images/order-status-3.png') }}" alt="">
                        </div>
                    </div>
                </div>
                <div class="bg-light-gray p-3 rounded-2 d-flex justify-content-between align-content-stretch">
                    <div class="w-100 d-flex flex-column gap-2">
                        <p class="fs-4 text-black fw-semibold m-0">{{ showCurrency(formatAmount($delayFinalAmount)) }}</p>
                        <p class="fs-6 m-0 text-secondary">{{ __('Delayed Final Orders') }}</p>
                    </div>
                    <div class="d-flex align-items-center">
                        <div class="bg-white p-2 rounded-2">
                            <img src="{{ asset('assets/images/order-status-4.png') }}" alt="">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="bg-white col-span-6 rounded-3 p-3">
            <div class="d-flex justify-content-between align-items-lg-center">
                <p class="fs-5 fw-semibold mb-0">{{ __('Top Pre-order Product') }}</p>
                <a href="{{ route('shop.preOrder.product.index') }}" class="btn btn-outline-primary">
                    <p>{{ __('View All') }}</p>
                </a>
            </div>
            <div class="my-3 overflow-scroll scrollbar_hide">
                <table class="table table_borderless table-hover align-middle" style="min-width: 1200px;">
                    <thead class="table-dark table_col">
                        <tr>
                            <th scope="col" style="width: 50px;">{{ __('SL.') }}</th>
                            <th scope="col" style="width: 600px">{{ __('Product') }}</th>
                            <th scope="col">{{ __('Brand') }}</th>
                            <th scope="col">{{ __('PrePaid') }}</th>
                            <th scope="col">{{ __('Total Sold') }}</th>
                            <th scope="col">{{ __('Total Earn') }}</th>
                        </tr>
                    </thead>
                    <tbody class="bottom_border table_row">
                        @php($thumbPlaceholder = asset('default/default.jpg'))
                        @foreach ($topProducts ?? [] as $key => $product)
                            <tr>
                                <td class="text-sm" scope="row">{{ $key + 1 }}.</td>
                                <td>
                                    <div class="d-flex justify-content-start align-items-center gap-2">
                                        <div style="height: 32px; width: 32px;">
                                            <img src="{{ $product->product_thumbnail ? Storage::url($product->product_thumbnail) : $thumbPlaceholder }}"
                                                onerror="this.onerror=null;this.src='{{ $thumbPlaceholder }}'" alt=""
                                                class="bg-cover rounded h-full w-full">
                                        </div>
                                        <p class="m-0 text-sm">{{ $product->name }}</p>
                                    </div>
                                </td>
                                <td class="text-sm">{{ $product->brand->name ?? 'N/A' }}</td>
                                <td class="text-sm"><span
                                        class="badge {{ $product->is_prepay ? 'bg-success' : 'bg-danger' }}">
                                        {{ $product->is_prepay ? 'Yes' : 'No' }}</td>
                                <td class="text-sm">{{ $product->total_sold }}</td>
                                <td class="text-sm">{{ showCurrency(formatAmount($product->total_revenue)) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </main>
@endsection
@push('scripts')
    <script>
        const currencySymbol = "{{ generaleSetting('setting')?->currency ?? '$' }}";
        const currencyPosition = "{{ generaleSetting('setting')?->currency_position ?? 'prefix' }}";
        const themeColor = (getComputedStyle(document.documentElement).getPropertyValue('--theme-color') || '#51AF5B').trim();
        function showCurrency(amount) {
            amount = amount ?? 0;
            amount = Math.round(amount);
            if (currencyPosition === "suffix") {
                return amount + currencySymbol;
            }
            return currencySymbol + amount;
        }
        var saleOptions = {
            chart: {
                type: 'area',
                height: 350,
                sparkline: {
                    enabled: false
                },
                toolbar: {
                    show: false
                },
                zoom: {
                    enabled: false
                }
            },
            series: [{
                name: 'Pre-orders',
                data: [0, 0, 0, 0, 0, 0, 0]
            }],
            stroke: {
                curve: 'smooth',
                width: 3,
                colors: [themeColor]
            },
            fill: {
                type: 'gradient',
                gradient: {
                    shadeIntensity: 1,
                    opacityFrom: 0.7,
                    opacityTo: 0.05,
                    stops: [0, 90, 100],
                    colorStops: [{
                        offset: 0,
                        color: themeColor,
                        opacity: 0.6
                    }, {
                        offset: 100,
                        color: '#FFFFFF',
                        opacity: 0.05
                    }]
                }
            },
            markers: {
                size: [0, 0, 0, 0, 0, 0, 8],
                colors: [themeColor],
                strokeColors: '#fff',
                strokeWidth: 3,
                hover: {
                    size: 10
                }
            },
            xaxis: {
                categories: [
                    "Mon", "Tue", "Wed", "Thu", "Fri", "Sat", "Sun"
                ],
                labels: {
                    style: {
                        colors: '#687387',
                        fontSize: '11px'
                    }
                },
                axisBorder: {
                    show: false
                },
                axisTicks: {
                    show: false
                }
            },
            yaxis: {
                labels: {
                    formatter: function(val) {
                        return Math.round(val) + "k";
                    },
                    style: {
                        colors: '#687387',
                        fontSize: '11px'
                    }
                },
                min: 0,
                tickAmount: 5
            },
            grid: {
                borderColor: '#e0e6ed',
                strokeDashArray: 4,
                yaxis: {
                    lines: {
                        show: true
                    }
                },
                xaxis: {
                    lines: {
                        show: false
                    }
                }
            },
            dataLabels: {
                enabled: false
            },
            legend: {
                show: false
            },
            tooltip: {
                y: {
                    formatter: function(val) {
                        return showCurrency(val) + " Amount";
                    }
                }
            }
        };
        var saleChart = new ApexCharts(document.querySelector("#sale-analytics-chart"), saleOptions);
        saleChart.render();
        function loadSaleAnalytics(type) {
            $.ajax({
                url: "{{ route('shop.preOrder.saleAnalytics') }}",
                data: {
                    type: type ?? 'daily'
                },
                success: function(res) {
                    saleChart.updateOptions({
                        xaxis: {
                            categories: res.labels
                        },
                        series: [{
                            name: "Pre-orders",
                            data: res.website
                        }]
                    });
                }
            });
        }
        $('.chart-btn-sm').on('click', function() {
            $('.chart-btn-sm').removeClass('seleted');
            $(this).addClass('seleted');
            let type = $(this).data('type');
            console.log(type, 'type');
            loadSaleAnalytics(type);
        });
        $(document).ready(function() {
            loadSaleAnalytics('monthly');
        });
    </script>
@endpush
