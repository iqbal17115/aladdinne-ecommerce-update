<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ __('Order Payment Receipt') }}</title>
    <style>
        body {
            font-family: "DejaVu Sans", "freeSerif", sans-serif;
            margin: 0;
            padding: 0;
            font-size: 12px;
            color: #334155;
        }
        .clearfix::after {
            content: "";
            clear: both;
            display: table;
        }
        .container {
            padding: 20px;
        }
        .header {
            margin-bottom: 20px;
        }
        .logo-box {
            float: left;
            width: 50%;
        }
        .business-details {
            float: right;
            width: 45%;
            text-align: right;
            padding-top: 10px;
        }
        .logo {
            width: 140px;
            height: auto;
        }
        .receipt-title-bar {
            background-color: #ffea00;
            padding: 8px;
            border-radius: 5px;
            text-align: right;
            margin: 20px 0;
        }
        .receipt-title-bar h1 {
            background-color: #ffffff;
            display: inline-block;
            padding: 5px 15px;
            margin: 0;
            border-radius: 4px;
            font-size: 18px;
            text-transform: uppercase;
        }
        .section {
            background: #f8fafc;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 15px;
        }
        .section-title {
            font-size: 14px;
            font-weight: bold;
            border-bottom: 1px dashed #cbd5e1;
            padding-bottom: 5px;
            margin-bottom: 10px;
            display: block;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        .data-table td {
            padding: 4px 0;
            vertical-align: top;
        }
        .items-table {
            margin-top: 10px;
        }
        .items-table th {
            background-color: #e2e8f0;
            padding: 8px;
            text-align: left;
            font-weight: bold;
        }
        .items-table td {
            padding: 8px;
            border-bottom: 1px solid #eee;
        }
        .items-table tfoot td {
            padding: 6px 8px;
            font-weight: bold;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .text-bold { font-weight: bold; }
        .text-muted { color: #64748b; }
        footer {
            text-align: center;
            margin-top: 30px;
            font-size: 11px;
            color: #94a3b8;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header clearfix">
            <div class="logo-box">
                <img src="{{ public_path('assets/logo.png') }}" class="logo" alt="Logo">
                <p style="margin-top: 10px;">{{ config('app.url') }}</p>
            </div>
            <div class="business-details">
                <span>{{ __('Address') }} :{{ __($generaleSetting?->address) }}</span> <br/>
                <span>{{ __('Email') }} :{{ $generaleSetting?->email }}</span> <br/>
                <span>{{ __('Mobile') }} :{{ $generaleSetting?->mobile }}</span>
            </div>
        </div>
        <div class="receipt-title-bar">
            <h1>{{ __('Payment Receipt') }}</h1>
        </div>
        @php
            $payment = $preOrder->payments()?->latest()->first();
            $transactionId = $payment?->payment_token ?? str_pad($preOrder->id, 6, '0', STR_PAD_LEFT);
            $user = $preOrder->customer?->user;
        @endphp
        <div class="section">
            <span class="section-title">{{ __('Payment Details') }}</span>
            <table class="data-table">
                <tr>
                    <td width="20%"><span class="text-bold">{{ __('Method') }}:</span></td>
                    <td width="30%">{{ $preOrder->payment_method->value }}</td>
                    <td width="20%"><span class="text-bold">{{ __('Paid Amount') }}:</span></td>
                    <td width="30%">{{ showCurrency($preOrder->paid_amount) }}</td>
                </tr>
                <tr>
                    <td><span class="text-bold">{{ __('Transaction ID') }}:</span></td>
                    <td>{{ $transactionId }}</td>
                    @if ($preOrder->payment_status->value != 'Paid')
                        <td><span class="text-bold">{{ __('Due Amount') }}:</span></td>
                        <td>{{ showCurrency($preOrder->payable_amount - $preOrder->paid_amount) }}</td>
                    @else
                        <td></td><td></td>
                    @endif
                </tr>
                <tr>
                    <td><span class="text-bold">{{ __('Status') }}:</span></td>
                    <td>{{ $preOrder->payment_status->value }}</td>
                    <td></td><td></td>
                </tr>
            </table>
        </div>
        <div class="section">
            <span class="section-title">{{ __('Customer Details') }}</span>
            <table class="data-table">
                <tr>
                    <td><span class="text-bold">{{ __('Name') }}:</span> {{ $user?->name }}</td>
                    <td><span class="text-bold">{{ __('Email') }}:</span> {{ $user?->email ?? '-' }}</td>
                    <td><span class="text-bold">{{ __('Phone') }}:</span> {{ $user?->phone ?? '-' }}</td>
                </tr>
            </table>
        </div>
        <div class="section">
            <span class="section-title">{{ __('Order Summary') }}</span>
            <table class="data-table" style="margin-bottom: 10px;">
                <tr>
                    <td><span class="text-muted">{{ __('Order ID') }}:</span> #{{ $preOrder->prefix . $preOrder->order_code }}</td>
                    <td><span class="text-muted">{{ __('Order Date') }}:</span> {{ $preOrder->created_at->format('F d, Y') }}</td>
                    <td class="text-right"><span class="text-muted">{{ __('Status') }}:</span> {{ $preOrder->payment_status->value }}</td>
                </tr>
            </table>
            <table class="items-table">
                <thead>
                    <tr>
                        <th>{{ __('Item') }}</th>
                        <th class="text-center">{{ __('Qty') }}</th>
                        <th class="text-center">{{ __('Price') }}</th>
                        <th class="text-center">{{ __('Total') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @php $item = $preOrder->preOrderItem; @endphp
                    <tr>
                        <td>{{ $item->product_name }}</td>
                        <td class="text-center">{{ $item->quantity }}</td>
                        <td class="text-center">{{ showCurrency($item->price) }}</td>
                        <td class="text-center">{{ showCurrency($item->quantity * $item->price) }}</td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3" class="text-right">{{ __('Subtotal') }}</td>
                        <td class="text-right">{{ showCurrency($preOrder->total_amount) }}</td>
                    </tr>
                    <tr>
                        <td colspan="3" class="text-right">{{ __('Shipping') }}</td>
                        <td class="text-right">{{ showCurrency($preOrder->delivery_charge) }}</td>
                    </tr>
                    <tr style="font-size: 14px; background: #eee;">
                        <td colspan="3" class="text-right">{{ __('Grand Total') }}</td>
                        <td class="text-right">{{ showCurrency($preOrder->payable_amount) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
        <footer>
            <p>{{ __('If you have any questions, contact us at') }} {{ $generaleSetting?->email }}</p>
        </footer>
    </div>
</body>
</html>
