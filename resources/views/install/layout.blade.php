<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Install') — {{ config('app.name', 'ReadyEcommerce') }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background: #f1f5f9; color: #0f172a; min-height: 100vh;
            display: flex; flex-direction: column; align-items: center; padding: 40px 16px;
        }
        .logo { height: 48px; margin-bottom: 24px; }
        .card {
            background: #fff; border-radius: 12px; width: 100%; max-width: 640px;
            box-shadow: 0 1px 3px rgba(15, 23, 42, .1); overflow: hidden;
        }
        .card-header { padding: 20px 28px; border-bottom: 1px solid #e2e8f0; }
        .card-header h1 { font-size: 18px; }
        .card-header p { color: #64748b; font-size: 13px; margin-top: 4px; }
        .card-body { padding: 28px; }
        .steps { display: flex; gap: 8px; margin-bottom: 24px; width: 100%; max-width: 640px; }
        .step {
            flex: 1; text-align: center; font-size: 12px; color: #94a3b8;
            padding: 8px 4px; border-bottom: 3px solid #e2e8f0;
        }
        .step.active { color: #4f46e5; border-color: #4f46e5; font-weight: 600; }
        .step.done { color: #16a34a; border-color: #16a34a; }
        table { width: 100%; border-collapse: collapse; }
        td { padding: 10px 4px; border-bottom: 1px solid #f1f5f9; font-size: 14px; }
        .badge { font-size: 12px; font-weight: 600; padding: 3px 10px; border-radius: 999px; }
        .badge.pass { background: #dcfce7; color: #166534; }
        .badge.fail { background: #fee2e2; color: #991b1b; }
        label { display: block; font-size: 13px; font-weight: 600; margin: 14px 0 6px; }
        input[type=text], input[type=email], input[type=password], input[type=url], input[type=number] {
            width: 100%; padding: 10px 12px; border: 1px solid #cbd5e1; border-radius: 8px;
            font-size: 14px; outline: none;
        }
        input:focus { border-color: #4f46e5; box-shadow: 0 0 0 3px rgba(79, 70, 229, .15); }
        .row { display: flex; gap: 14px; }
        .row > div { flex: 1; }
        .btn {
            display: inline-block; background: #4f46e5; color: #fff; border: 0; cursor: pointer;
            padding: 11px 22px; border-radius: 8px; font-size: 14px; font-weight: 600;
            text-decoration: none; margin-top: 22px;
        }
        .btn:hover { background: #4338ca; }
        .btn[disabled] { background: #cbd5e1; cursor: not-allowed; }
        .btn-secondary { background: #f1f5f9; color: #334155; }
        .btn-secondary:hover { background: #e2e8f0; }
        .alert { border-radius: 8px; padding: 12px 16px; font-size: 13px; margin-bottom: 18px; }
        .alert-danger { background: #fee2e2; color: #991b1b; }
        .alert-warning { background: #fef9c3; color: #854d0e; }
        .alert-success { background: #dcfce7; color: #166534; }
        .muted { color: #64748b; font-size: 13px; }
        .check { margin: 16px 0 0; display: flex; align-items: center; gap: 8px; font-size: 14px; }
        .footer-note { margin-top: 20px; font-size: 12px; color: #94a3b8; }
    </style>
</head>
<body>
    {{-- Static asset — the installer runs before the DB exists --}}
    <img class="logo" src="{{ asset('assets/logo.png') }}" alt="{{ config('app.name', 'ReadyEcommerce') }}">

    @php($current = $step ?? 1)
    <div class="steps">
        @foreach (['Requirements', 'Database', 'Migrate', 'Admin', 'Done'] as $i => $label)
            <div class="step {{ $current == $i + 1 ? 'active' : ($current > $i + 1 ? 'done' : '') }}">{{ $label }}</div>
        @endforeach
    </div>

    <div class="card">
        <div class="card-header">
            <h1>@yield('heading')</h1>
            <p>@yield('subheading')</p>
        </div>
        <div class="card-body">
            @if ($errors->any())
                <div class="alert alert-danger">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif
            @yield('content')
        </div>
    </div>
    <div class="footer-note">{{ config('app.name', 'ReadyEcommerce') }} installer</div>
</body>
</html>
