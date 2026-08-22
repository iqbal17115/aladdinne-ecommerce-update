@php
    $lowStockCount = $lowStockProducts->count();
@endphp

@if ($lowStockCount && $generaleSetting?->low_stock_alert)
    <div class="low-stock-alert alert mb-3" role="alert">
        <div class="low-stock-alert-header">
            <div class="d-flex align-items-center gap-2">
                <i class="fa-solid fa-circle-exclamation low-stock-icon"></i>
                <div>
                    <strong class="low-stock-title">{{ __('Low Stock Alert') }}</strong>
                    <span class="low-stock-subtitle">{{ $lowStockCount }} {{ __('product(s) need attention') }}</span>
                </div>
            </div>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <div class="low-stock-body">
            @foreach ($lowStockProducts as $product)
                <div class="low-stock-item">
                    <div class="low-stock-item-info">
                        @if (! empty($product->thumbnail))
                            <img src="{{ $product->thumbnail }}" alt="{{ $product->name }}" class="low-stock-thumb" loading="lazy">
                        @else
                            <div class="low-stock-thumb low-stock-thumb-placeholder">
                                <i class="fa-solid fa-box"></i>
                            </div>
                        @endif
                        <div>
                            <div class="low-stock-item-name">{{ Str::limit($product->name, 30) }}</div>
                            @if (! empty($product->sku))
                                <div class="low-stock-item-sku">{{ __('SKU') }}: {{ $product->sku }}</div>
                            @endif
                        </div>
                    </div>
                    <div class="low-stock-qty-badge">
                        <span class="low-stock-qty-value">{{ $product->quantity }}</span>
                        <span class="low-stock-qty-label">{{ __('left') }}</span>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endif

@once
    @push('css')
        <style>
            .low-stock-alert {
                background: white;
                border: 1px solid #e4c66c;
                border-radius: 12px;
                overflow: hidden;
                box-shadow: 0 4px 12px rgba(255, 193, 7, 0.2);
            }

            .low-stock-alert-header {
                background: linear-gradient(135deg, #f3ce46 0%, #ebc140 100%);
                padding: 14px 20px;
                display: flex;
                align-items: center;
                justify-content: space-between;
            }

            .low-stock-icon {
                font-size: 24px;
                color: #fff;
                animation: lowStockPulse 1.5s ease-in-out infinite;
            }

            @keyframes lowStockPulse {
                0%, 100% {
                    opacity: 1;
                    transform: scale(1);
                }

                50% {
                    opacity: 0.7;
                    transform: scale(1.1);
                }
            }

            .low-stock-title {
                font-size: 16px;
                color: #fff;
                display: block;
                line-height: 1.2;
            }

            .low-stock-subtitle {
                font-size: 12px;
                color: rgba(255, 255, 255, 0.9);
            }

            .low-stock-body {
                padding: 12px 16px;
                display: flex;
                flex-wrap: wrap;
                gap: 8px;
            }

            .low-stock-item {
                display: flex;
                align-items: center;
                justify-content: space-between;
                background: #fff;
                border: 1px solid #ffe8a1;
                border-radius: 8px;
                padding: 8px 12px;
                flex: 1 1 calc(50% - 4px);
                min-width: 200px;
                transition: all 0.2s ease;
            }

            .low-stock-item:hover {
                border-color: #ffc107;
                box-shadow: 0 2px 8px rgba(255, 193, 7, 0.15);
                transform: translateY(-1px);
            }

            .low-stock-item-info {
                display: flex;
                align-items: center;
                gap: 10px;
                min-width: 0;
            }

            .low-stock-thumb {
                width: 36px;
                height: 36px;
                border-radius: 6px;
                object-fit: cover;
                flex-shrink: 0;
            }

            .low-stock-thumb-placeholder {
                background: #fff3cd;
                display: flex;
                align-items: center;
                justify-content: center;
                color: #ff8c00;
                font-size: 16px;
            }

            .low-stock-item-name {
                font-size: 13px;
                font-weight: 600;
                color: #333;
                line-height: 1.2;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }

            .low-stock-item-sku {
                font-size: 11px;
                color: #999;
                margin-top: 1px;
            }

            .low-stock-qty-badge {
                background: linear-gradient(135deg, #dc3545 0%, #ff6b6b 100%);
                color: #fff;
                border-radius: 20px;
                padding: 4px 12px;
                display: flex;
                flex-direction: column;
                align-items: center;
                line-height: 1;
                flex-shrink: 0;
                min-width: 48px;
            }

            .low-stock-qty-value {
                font-size: 16px;
                font-weight: 700;
            }

            .low-stock-qty-label {
                font-size: 9px;
                text-transform: uppercase;
                letter-spacing: 0.5px;
                opacity: 0.85;
            }
        </style>
    @endpush
@endonce
