@extends('layouts.app')
@section('content')
    @push('css')
        <link rel="stylesheet" href="{{ asset('modules/report/css/report.css') }}">
    @endpush
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    <main class="p-4 grid grid-cols-6 gap-4">
        <div class="widget_container col-span-6 rounded-3 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 p-3"
            style="background:url('{{ asset('assets/reportImage/widget-bg.png') }}') no-repeat center / cover">

            <!-- widget 1 -->
            <div class="glassmorphism">
                <p class="fs-4 text-white fw-semibold mb-3">{{ showCurrency(formatAmount($totalSaleAmount)) }}</p>
                <div class="d-flex justify-content-between">
                    <p class="fs-6 text-white fw-normal mb-0">{{ __('Total Sales All Time') }}</p>
                    <div class="glassmorphismIconBox">
                        <img src="{{ asset('assets/reportImage/wallet.svg') }}" alt="">
                    </div>
                </div>
            </div>

            <!-- widget 2 -->
            <div class="glassmorphism">
                <p class="fs-4 text-white fw-semibold mb-3">{{ showCurrency(formatAmount($payOuts)) }}</p>
                <div class="d-flex justify-content-between">
                    <p class="fs-6 text-white fw-normal mb-0">{{ __('Total Payouts') }}</p>
                    <div class="glassmorphismIconBox">
                        <img src="{{ asset('assets/reportImage/payment.svg') }}" alt="">
                    </div>
                </div>
            </div>

            <!-- widget 3 -->
            <div class="glassmorphism">
                <p class="fs-4 text-white fw-semibold mb-3">{{ showCurrency(formatAmount($stockProductAmount)) }}</p>
                <div class="d-flex justify-content-between">
                    <p class="fs-6 text-white fw-normal mb-0">{{ __('Stock Product Amount') }}</p>
                    <div class="glassmorphismIconBox">
                        <img src="{{ asset('assets/reportImage/stock.svg') }}" alt="">
                    </div>
                </div>
            </div>

            <!-- widget 4 -->
            <div class="glassmorphism">
                <p class="fs-4 text-white fw-semibold mb-3">{{ showCurrency(formatAmount($totalPurchaseAmount)) }}</p>
                <div class="d-flex justify-content-between">
                    <p class="fs-6 text-white fw-normal mb-0">{{ __('Product Purchase') }}</p>
                    <div class="glassmorphismIconBox">
                        <img src="{{ asset('assets/reportImage/purchase.svg') }}" alt="">
                    </div>
                </div>
            </div>

            <!-- widget 5 -->
            <div class="glassmorphism">
                <p class="fs-4 text-white fw-semibold mb-3">{{ showCurrency(formatAmount($totalRefundAmount)) }}</p>
                <div class="d-flex justify-content-between">
                    <p class="fs-6 text-white fw-normal mb-0">{{ __('Refunded Amount') }}</p>
                    <div class="glassmorphismIconBox">
                        <img src="{{ asset('assets/reportImage/amount.svg') }}" alt="">
                    </div>
                </div>
            </div>

            <!-- widget 6 -->
            <div class=" glassmorphism">
                <p class="fs-4 text-white fw-semibold mb-3">{{ showCurrency(formatAmount($totalCancelAmount)) }}</p>
                <div class="d-flex justify-content-between">
                    <p class="fs-6 text-white fw-normal mb-0">{{ __('Cancelation Amount') }}</p>
                    <div class="glassmorphismIconBox">
                        <img src="{{ asset('assets/reportImage/x.svg') }}" alt="">
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white col-span-6 rounded-3 p-3">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <p class="fs-5 fw-semibold mb-0">{{ __('Earning Report') }}</p>
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex justify-content-between align-items-center gap-2">
                        <button type="button" class="btn-sm-one earning-btn seleted" data-type="daily">
                            <p>{{ __('Daily') }}</p>
                        </button>
                        <button type="button" class="btn-sm-one earning-btn" data-type="monthly">
                            <p>{{ __('Monthly') }}</p>
                        </button>
                        <button type="button" class="btn-sm-one earning-btn" data-type="yearly">
                            <p>{{ __('Yearly') }}</p>
                        </button>
                    </div>
                </div>
            </div>
            <div class="border p-3 rounded">
                <div id="earning-analytics-chart" style="width: 100%; max-height: 564px"></div>
            </div>
        </div>

        <div class="col-span-6 lg:col-span-3 p-3 rounded-3 bg-white">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <p class="fs-5 fw-semibold mb-0">{{ __('Sale Analytics') }}</p>
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex justify-content-between align-items-center gap-2">
                        <button type="button" class="btn-sm-one salebtn seleted" data-type="daily">
                            <p>{{ __('Daily') }}</p>
                        </button>
                        <button type="button" class="btn-sm-one salebtn" data-type="monthly">
                            <p>{{ __('Monthly') }}</p>
                        </button>
                        <button type="button" class="btn-sm-one salebtn" data-type="yearly">
                            <p>{{ __('Yearly') }}</p>
                        </button>
                    </div>
                </div>
            </div>
            <div>
                <div id="sale-analytics-chart" style="width: 100%; max-height: 564px"></div>
            </div>
        </div>

        <div class="col-span-6 lg:col-span-3 p-3 rounded-3 bg-white">
            <div class="text-end mb-4">
                <p class="fs-5 fw-semibold mb-0">{{ __('Cost Analytics') }}</p>
            </div>
            <div class="grid grid-cols-2 gap-6">
                <div id="cost-analytics-chart" style="width: 100%; max-height: 250px"></div>
                <div class="d-flex flex-column gap-3">
                    <div class="border-bottom border-light-subtle pb-3">
                        <p class="fs-2 mb-0">{{__('Total')}}: <strong style="color: #E46259;" id="costTotalAmount">$0</strong></p>
                    </div>
                    <div class="custom_legend_container">
                        <div>
                            <p class="legend_name">{{ __('Payouts') }}</p>
                            <span class="legend_symbol legend_1"></span>
                        </div>
                        <p class="legend_value" id="payOutAmount">$0</p>
                    </div>
                    <div class="custom_legend_container">
                        <div>
                            <p class="legend_name">{{ __('Rider') }}</p>
                            <span class="legend_symbol legend_2"></span>
                        </div>
                        <p class="legend_value" id="riderCost">$0</p>
                    </div>

                    <div class="custom_legend_container">
                        <div>
                            <p class="legend_name">{{ __('Refund') }}</p>
                            <span class="legend_symbol legend_3"></span>
                        </div>
                        <p class="legend_value" id="refundAmount">$0</p>
                    </div>

                    <div class="custom_legend_container">
                        <div>
                            <p class="legend_name">{{ __('Purchase') }}</p>
                            <span class="legend_symbol legend_4"></span>
                        </div>
                        <p class="legend_value" id="purchaseAmount">$0</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-span-6 md:col-span-3 lg:col-span-2 p-3 rounded-3 bg-white">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <p class="title_sm">{{ __('Top Categories') }}</p>
                <p class="order_state">
                    {{ __('Total Categories') }} : {{ $totalCategories }}
                </p>
            </div>
            <div class="d-flex flex-column gap-2">
                @foreach ($topSellingCategories ?? [] as $category)
                    <div class="category_card">
                        <div>
                            <img src="{{ $category->thumbnail }}" alt="">
                            <p>{{ Str::limit($category->name, 30, '...') }}</p>
                        </div>
                        <span class="sell_status">{{ __('Sell') }} : {{ $category->sell_count }}</span>
                    </div>
                @endforeach
            </div>
        </div>


        <div class="col-span-6 md:col-span-3  lg:col-span-2 p-3 rounded-3 bg-white">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <p class="title_sm">{{ __('Top Brand') }}</p>
                <p class="order_state">
                    {{ __('Total Brand') }} : {{ $totalBrand }}
                </p>
            </div>

            <div class="d-flex flex-column gap-2">
                @foreach ($topSellingBrands ?? [] as $brand)
                    <div class="category_card">
                        <div>
                            <img src="{{ asset('assets/icons-admin/brand.svg') }}" alt=""
                                style="width:20px !important;height:20px !important;border:none !important">
                            <p>{{ Str::limit($brand->name, 30, '...') }}</p>
                        </div>
                        <span class="sell_status">{{ __('Sell') }} : {{ $brand->sell_count }}</span>
                    </div>
                @endforeach


            </div>
        </div>

        <div class="col-span-6 md:col-span-3 lg:col-span-2 p-3 rounded-3 bg-white">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <p class="title_sm">{{ __('Most Selling') }}</p>
            </div>
            <div class="d-flex flex-column gap-2">
                @foreach ($topSellingProducts ?? [] as $product)
                    <div class="selling_card">
                        <div>
                            <img src="{{ $product->thumbnail }}" alt="">
                            <p>{{ Str::limit($product->name, 30, '...') }}</p>
                        </div>
                        <span class="sell_status">{{ __('Sell') }} : {{ $product->orders_count }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </main>
@endsection

@push('scripts')
    <script src="{{ asset('modules/report/js/report.js') }}"></script>

    <script>
        "use strict";
        var earningChartOptions = {
            legend: {
                position: 'top',
                horizontalAlign: 'center',
                fontSize: '14px',
                offsetY: 7
            },
            series: [{
                name: 'Products Sales',
                data: [0, 0, 0, 0, 0, 0, 0]
            }, {
                name: 'Purchase',
                data: [0, 0, 0, 0, 0, 0, 0]
            }, {
                name: 'Payouts',
                data: [0, 0, 0, 0, 0, 0, 0]
            }, {
                name: 'Total Orders',
                data: [0, 0, 0, 0, 0, 0, 0]
            }],
            colors: ['#84CD8C', '#D482F4', '#F07D7D', '#8E83F5'],
            chart: {
                type: 'bar',
                height: 350,
            },
            plotOptions: {
                bar: {
                    horizontal: false,
                    columnWidth: '60%',
                    borderRadius: 5,
                    borderRadiusApplication: 'end'
                },
            },
            dataLabels: {
                enabled: false
            },
            stroke: {
                show: true,
                width: 2,
                colors: ['transparent']
            },
            xaxis: {
                type: 'category',
                categories: [
                    "Mon", "Tue", "Wed", "Thu", "Fri", "Sat", "Sun"
                ],
                labels: {
                    style: {
                        fontSize: '10px',
                        fontWeight: 400,
                        colors: '#687387'
                    }
                }
            },
            yaxis: {},
            fill: {
                opacity: 1
            },
            tooltip: {
                y: {
                    formatter: function(val) {
                        return "$ " + val + " K"
                    }
                }
            }
        };

        var costOptions = {
            series: [0, 0, 0, 0],
            chart: {
                type: 'donut',
                height: 350,
                toolbar: {
                    show: false
                }
            },
            labels: ['Payouts', 'Rider', 'Refund', 'Purchase'],
            colors: ['#51AF5B', '#EFC33D', '#F35E54', '#4086E1'],
            plotOptions: {
                pie: {
                    donut: {
                        size: '35%',
                        labels: {
                            show: false
                        }
                    }
                }
            },
            dataLabels: {
                enabled: false
            },
            legend: {
                show: false
            }
        };

        var saleOptions = {
            chart: {
                height: 350,
                type: 'line'
            },
            colors: ['#D3D3D3', '#51AF5B'],
            series: [{
                    name: 'Website',
                    type: 'column',
                    data: []
                },
                {
                    name: 'POS',
                    type: 'line',
                    data: []
                }
            ],
            stroke: {
                width: [0, 4]
            },
            plotOptions: {
                bar: {
                    columnWidth: '20%',
                    borderRadius: 10
                }
            },
            labels: [],
            dataLabels: {
                enabled: true,
                enabledOnSeries: [1]
            },
            yaxis: {
                labels: {
                    formatter: val => val + ' K'
                }
            },
            tooltip: {
                y: {
                    formatter: val => val + ' K'
                }
            }
        };

        // chart
        var earningChart = new ApexCharts(document.querySelector("#earning-analytics-chart"), earningChartOptions);
        earningChart.render();
        var costChart = new ApexCharts(document.querySelector("#cost-analytics-chart"), costOptions);
        costChart.render();
        var saleChart = new ApexCharts(document.querySelector("#sale-analytics-chart"), saleOptions);
        saleChart.render();

        function loadEarningAnalytics(type) {
            $.ajax({
                url: "{{ route('report.earningAnalytics') }}",
                data: {
                    type: type
                },
                success: function(res) {
                    earningChart.updateOptions({
                        xaxis: {
                            categories: res.labels
                        },
                        series: [{
                                name: 'Products Sales',
                                data: res.sales || []
                            },
                            {
                                name: 'Purchase',
                                data: res.purchase || []
                            },
                            {
                                name: 'Payouts',
                                data: res.payouts || []
                            },
                            {
                                name: 'Total Orders',
                                data: res.orders || []
                            }
                        ]
                    });
                }
            });
        }

        $('.earning-btn').on('click', function() {
            $('.earning-btn').removeClass('seleted');
            $(this).addClass('seleted');
            let type = $(this).data('type');
            loadEarningAnalytics(type);
        });

        function loadSaleAnalytics(type) {
            $.ajax({
                url: "{{ route('report.saleAnalytics') }}",
                data: {
                    type: type ?? 'daily'
                },
                success: function(res) {
                    let websiteData = [];
                    let posData = [];
                    res.labels.forEach(label => {
                        websiteData.push(res.website[label] ?? 0);
                        posData.push(res.pos[label] ?? 0);
                    });
                    saleChart.updateOptions({
                        labels: res.labels,
                        series: [{
                                name: 'Website',
                                type: 'column',
                                data: websiteData
                            },
                            {
                                name: 'POS',
                                type: 'line',
                                data: posData
                            }
                        ]
                    });
                }
            });
        }
        $('.salebtn').on('click', function() {
            $('.salebtn').removeClass('seleted');
            $(this).addClass('seleted');
            let type = $(this).data('type');
            loadSaleAnalytics(type);
        });

        function fetchCostData() {
            $.ajax({
                url: "{{ route('report.costAnalytics') }}",
                type: 'GET',
                success: function(res) {
                    costChart.updateOptions({
                        series: [
                            res.Payouts ?? 0,
                            res.Rider ?? 0,
                            res.Refund ?? 0,
                            res.Purchase ?? 0
                        ]
                    });
                    $('#payOutAmount').text('$' + (res.Payouts ?? 0).toLocaleString());
                    $('#riderCost').text('$' + (res.Rider ?? 0).toLocaleString());
                    $('#refundAmount').text('$' + (res.Refund ?? 0).toLocaleString());
                    $('#purchaseAmount').text('$' + (res.Purchase ?? 0).toLocaleString());
                    $('#costTotalAmount').text('$' + (res.grantTotal ?? 0).toLocaleString());
                },
                error: function(err) {
                    console.error('Failed to fetch cost data', err);
                }
            });
        }
        $(document).ready(function() {
            fetchCostData();
            loadSaleAnalytics('daily');
            loadEarningAnalytics('daily');
        });
    </script>
@endpush
