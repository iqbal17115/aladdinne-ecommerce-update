<!DOCTYPE html>

<html>

<head>
    <meta charset="UTF-8">
    <title>Low Stock Alert</title>
</head>

<body style="margin:0;padding:20px;background:#f5f7fa;font-family:Arial,Helvetica,sans-serif;">

    <div
        style="max-width:600px;margin:0 auto;background:#ffffff;border-radius:10px;padding:40px 30px;text-align:center;box-shadow:0 2px 10px rgba(0,0,0,0.05);">

        <div style="font-size:48px;margin-bottom:15px;">⚠️</div>

        <h2 style="margin:0 0 15px;color:#e67e22;">
            Low Stock Alert
        </h2>

        <p style="font-size:16px;line-height:1.8;color:#555;margin:0;">
            The following products are low on stock:
        </p>

        <table style="width:100%;margin:20px 0;border-collapse:collapse;text-align:left;font-size:14px;">
            <thead>
                <tr style="background:#f8f9fa;">
                    <th style="padding:10px;border:1px solid #dee2e6;">Product</th>
                    <th style="padding:10px;border:1px solid #dee2e6;">Remaining Qty</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($products as $product)
                    <tr>
                        <td style="padding:10px;border:1px solid #dee2e6;">{{ $product['name'] }}</td>
                        <td style="padding:10px;border:1px solid #dee2e6;">{{ $product['quantity'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div style="margin:30px 0;border-top:1px solid #eeeeee;"></div>

        <p style="font-size:14px;color:#777;line-height:1.8;margin:0;">
            Please review your inventory and restock the affected product(s) as soon as possible to avoid stock
            shortages.
        </p>

    </div>

    <div class="email-footer" style="text-align:center;padding:20px 0;color:#888;font-size:13px;">
        <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
        <p>
            <a href="{{ config('app.url') . '/privacy-policy' }}">Privacy Policy</a> |
            <a href="{{ config('app.url') . '/contact-us' }}">Contact Support</a>
        </p>
    </div>

</body>

</html>
