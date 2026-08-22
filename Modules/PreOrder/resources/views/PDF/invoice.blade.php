@php
    $address = $preOrder?->address;
    $user = $preOrder->customer?->user;
    $item = $preOrder->preOrderItem;
@endphp
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Invoice') }} - {{ $preOrder->order_code }}</title>
    <style>
        body {
            color: #303042;
            background-color: #F9FAFC;
            font-size: 14px;
            margin: 0;
            padding: 20px;
            line-height: 1.4;
        }
        .w-full {
            width: 100%;
        }
        .text-right {
            text-align: right;
        }
        .text-left {
            text-align: left;
        }
        .text-center {
            text-align: center;
        }
        .fw-bold {
            font-weight: bold;
        }
        .text-gray {
            color: #5E6470;
        }
        .clearfix::after {
            content: "";
            clear: both;
            display: table;
        }
        .header {
            margin-bottom: 0px;
            padding: 10px 0px 20px 20px
        }
        .w-50 {
            width: 50%;
        }
        .float-left {
            float: left;
        }
        .float-right {
            float: right;
        }
        .logo-section {
            float: left;
            width: 60%;
        }
        .logo {
            width: 80px;
            height: 80px;
            float: left;
            margin-right: 15px;
        }
        .address-section {
            float: left;
            width: 35%;
            text-align: right;
        }
        .main-card {
            background: #fff;
            padding: 25px;
            border-radius: 12px;
            min-height: 600px;
        }
        .billing-grid {
            margin-bottom: 30px;
        }
        .bill-to {
            float: left;
            width: 60%;
        }
        .invoice-summary {
            float: right;
            width: 35%;
            text-align: right;
        }
        .amount-big {
            font-size: 24px;
            color: #3546AE;
            font-weight: bold;
            margin: 5px 0;
        }
        .qr-code {
            width: 70px;
            margin-top: 10px;
        }
        .details-table {
            margin-bottom: 20px;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }
        .details-table th {
            color: #5E6470;
            font-size: 12px;
            text-transform: uppercase;
        }
        .items-table {
            border-collapse: collapse;
            margin-top: 15px;
        }
        .items-table th {
            background: #3546AE;
            color: #ffffff;
            padding: 10px;
            font-weight: 600;
        }
        .items-table td {
            padding: 12px 10px;
            border-bottom: 1px solid #CFCFCF;
        }
        .product-img {
            width: 35px;
            height: 35px;
            vertical-align: middle;
            margin-right: 8px;
            border-radius: 4px;
        }
        .totals-wrapper {
            float: right;
            width: 300px;
            margin-top: 20px;
        }
        .totals-table {
            width: 100%;
            border-collapse: collapse;
        }
        .totals-table td {
            padding: 4px 0;
        }
        .border-top td {
            border-top: 1px solid #CFCFCF;
            padding-top: 6px;
        }
        .grand-total {
            font-size: 18px;
            font-weight: bold;
            color: #3546AE;
        }
        .footer {
            position: fixed;
            bottom: 30px;
            width: 100%;
        }
        .signature-box {
            border-top: 1px solid #303042;
            width: 150px;
            text-align: center;
            float: right;
        }
    </style>
</head>
<body>
    <div class="header clearfix">
        <div class="logo-section">
            <img class="logo" src="{{ $generaleSetting?->favicon ?? asset('assets/favicon.png') }}" alt="logo" />
        </div>
        <div class="address-section">
            <h2 style="font-size: 20px;">{{ $generaleSetting?->name ?? config('app.name') }}</h2>
            <div class="text-gray" style="font-size: 12px;">
                <div>{{ __('Address') }} :{{ $generaleSetting?->address }}</div>
                <div>{{ __('Email') }} :{{ $generaleSetting?->email }}</div>
                <div>{{ __('Mobile') }} :{{ $generaleSetting?->mobile }}</div>
                <div>{{ config('app.url') }}</div>
            </div>
        </div>
    </div>
    <div class="main-card">
        <div class="billing-grid clearfix">
            <div class="bill-to">
                <div class="text-gray">{{ __('Bill To') }}:</div>
                <div class="fw-bold" style="font-size: 16px;">{{ $user?->name }}</div>
                <div class="text-gray" style="margin-top: 5px;">
                    {{ collect([$address?->address_type, $address?->address_line, $address?->address_line2, $address?->area])->filter()->implode(', ') }}
                </div>
                <div style="margin-top: 5px;">
                    <span class="text-gray">{{ __('Email') }}:</span> {{ $user?->email }}<br>
                    <span class="text-gray">{{ __('Phone') }}:</span> {{ $user?->phone }}
                </div>
            </div>
            <div class="invoice-summary">
                <div class="text-gray">{{ __('Invoice Amount') }}</div>
                <div class="amount-big">{{ showCurrency($preOrder->payable_amount) }}</div>
                <img class="qr-code" src="{{ $qrCodeImage }}" alt="QR">
            </div>
        </div>
        <table class="w-full details-table">
            <thead>
                <tr>
                    <th class="text-left">{{ __('Payment Method') }}</th>
                    <th class="text-left">{{ __('Invoice Number') }}</th>
                    <th class="text-left">{{ __('Invoice Date') }}</th>
                    <th class="text-right">{{ __('Order Date') }}</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{ $preOrder->payment_method->value }}</td>
                    <td>#{{ $preOrder->order_code }}</td>
                    <td>{{ now()->format('d M, Y') }}</td>
                    <td class="text-right">{{ $preOrder->created_at->format('d M, Y') }}</td>
                </tr>
            </tbody>
        </table>
        <table class="w-full items-table">
            <thead>
                <tr>
                    <th class="text-left">{{ __('Item') }}</th>
                    <th class="text-center">{{ __('Rate') }}</th>
                    <th class="text-center">{{ __('Discount') }}</th>
                    <th class="text-center">{{ __('Qty') }}</th>
                    <th class="text-right">{{ __('Total') }}</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <img src="{{ $item?->product?->thumbnail }}" class="product-img">
                        <span class="fw-bold">{{ $item->product_name }}</span>
                    </td>
                    <td class="text-center">{{ showCurrency($item->product_price) }}</td>
                    <td class="text-center">{{ showCurrency($item->discount) }}</td>
                    <td class="text-center">{{ $item->quantity }}</td>
                    <td class="text-right fw-bold">{{ showCurrency($item->quantity * $item->price) }}</td>
                </tr>
            </tbody>
        </table>
        <div class="totals-wrapper">
            <table class="totals-table">
                <tr>
                    <td>{{ __('Sub Total') }}</td>
                    <td class="text-right">{{ showCurrency($preOrder->total_amount) }}</td>
                </tr>
                <tr>
                    <td>{{ __('Delivery Charge') }}</td>
                    <td class="text-right">{{ showCurrency($preOrder->delivery_charge) }}</td>
                </tr>
                <tr class="border-top">
                    <td><strong>{{ __('Total Amount') }}</strong></td>
                    <td class="text-right"><strong>{{ showCurrency($preOrder->payable_amount) }}</strong></td>
                </tr>
                <tr>
                    <td>{{ __('Paid Amount') }}</td>
                    <td class="text-right">{{ showCurrency($preOrder->paid_amount) }}</td>
                </tr>
            </table>
        </div>
    </div>
    <div class="footer">
        <div style="float: left; width: 60%;padding-left:10px;" class="text-gray">
            {{ __('Thank you for your business!') }}
        </div>
        <div class="signature-box">
            {{ __('Authorized Signature') }}
        </div>
    </div>
</body>
</html>
