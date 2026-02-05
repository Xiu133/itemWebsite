<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>訂單詳情 — MONO</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@300;400;500;600&family=Noto+Sans+TC:wght@300;400;500&display=swap" rel="stylesheet">
    @vite(['resources/css/ecommerce/style.css', 'resources/css/merchant/dashboard.css', 'resources/css/product/style.css', 'resources/css/orders/manage.css', 'resources/js/orders/manage.js'])
</head>
<body>
    <header>
        <div class="header-inner">
            <a href="/" class="logo">MONO</a>
            <nav class="merchant-nav">
                <a href="{{ route('merchant.dashboard') }}">賣家中心</a>
            </nav>
            <div class="header-actions">
                <div class="user-menu">
                    <button class="icon-btn user-btn" id="user-btn">
                        <svg viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    </button>
                    <div class="user-dropdown" id="user-dropdown">
                        <div class="user-info">
                            <div class="user-name">{{ auth()->user()->name }}</div>
                            <div class="user-email">{{ auth()->user()->email }}</div>
                        </div>
                        <a href="/" class="user-menu-item">
                            <svg viewBox="0 0 24 24"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                            返回商店
                        </a>
                        <div class="user-menu-divider"></div>
                        <form action="{{ route('merchant.logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="user-menu-item logout-btn">
                                <svg viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                                登出
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <main class="dashboard-page">
        <div class="dashboard-container">
            <div style="margin-bottom: 1.5rem;">
                <a href="{{ route('manage-orders.index') }}" class="back-link">
                    <svg viewBox="0 0 24 24"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                    返回訂單列表
                </a>
            </div>

            @if(session('success'))
                <div class="success-message" style="margin-bottom: 1.5rem;">
                    {{ session('success') }}
                </div>
            @endif

            <div class="products-section">
                <div class="section-header">
                    <h2 class="section-title">訂單 {{ $order->order_number }}</h2>
                    <span class="order-status status-{{ $order->status }}">{{ $order->status_text }}</span>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-top: 1.5rem;">
                    <div class="order-detail-section">
                        <h3>訂單資訊</h3>
                        <div class="detail-row">
                            <span class="detail-label">訂單編號</span>
                            <span class="detail-value">{{ $order->order_number }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">訂單日期</span>
                            <span class="detail-value">{{ $order->created_at->format('Y-m-d H:i:s') }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">付款方式</span>
                            <span class="detail-value">{{ $order->payment_method_text ?? '-' }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">付款時間</span>
                            <span class="detail-value">{{ $order->paid_at ? $order->paid_at->format('Y-m-d H:i:s') : '-' }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">更新狀態</span>
                            <select class="status-select" onchange="updateOrderStatus({{ $order->id }}, this)">
                                <option value="pending" {{ $order->status === 'pending' ? 'selected' : '' }}>待付款</option>
                                <option value="paid" {{ $order->status === 'paid' ? 'selected' : '' }}>已付款</option>
                                <option value="processing" {{ $order->status === 'processing' ? 'selected' : '' }}>處理中</option>
                                <option value="shipped" {{ $order->status === 'shipped' ? 'selected' : '' }}>已出貨</option>
                                <option value="completed" {{ $order->status === 'completed' ? 'selected' : '' }}>已完成</option>
                                <option value="cancelled" {{ $order->status === 'cancelled' ? 'selected' : '' }}>已取消</option>
                            </select>
                        </div>
                    </div>

                    <div class="order-detail-section">
                        <h3>收件資訊</h3>
                        <div class="detail-row">
                            <span class="detail-label">收件人</span>
                            <span class="detail-value">{{ $order->shipping_name }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">電話</span>
                            <span class="detail-value">{{ $order->shipping_phone }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">地址</span>
                            <span class="detail-value">{{ $order->shipping_city }}{{ $order->shipping_district }}{{ $order->shipping_address }}</span>
                        </div>
                    </div>
                </div>

                <!-- 物流資訊 -->
                <div class="order-detail-section" id="logistics-section">
                    <h3>
                        <svg viewBox="0 0 24 24" style="width: 20px; height: 20px; vertical-align: middle; margin-right: 0.5rem;">
                            <rect x="1" y="3" width="15" height="13" fill="none" stroke="currentColor" stroke-width="2"/>
                            <polygon points="16 8 20 8 23 11 23 16 16 16 16 8" fill="none" stroke="currentColor" stroke-width="2"/>
                            <circle cx="5.5" cy="18.5" r="2.5" fill="none" stroke="currentColor" stroke-width="2"/>
                            <circle cx="18.5" cy="18.5" r="2.5" fill="none" stroke="currentColor" stroke-width="2"/>
                        </svg>
                        物流資訊
                    </h3>

                    @if($order->logistics_trade_no)
                        <div class="detail-row">
                            <span class="detail-label">物流狀態</span>
                            <span class="detail-value">
                                <span class="order-status status-{{ $order->logistics_status ?? 'created' }}">
                                    {{ $order->logistics_status_text }}
                                </span>
                            </span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">物流類型</span>
                            <span class="detail-value">{{ $order->logistics_type_text }} - {{ $order->logistics_sub_type_text }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">物流編號</span>
                            <span class="detail-value" style="font-family: monospace;">{{ $order->all_pay_logistics_id ?? '-' }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">交易編號</span>
                            <span class="detail-value" style="font-family: monospace;">{{ $order->logistics_trade_no }}</span>
                        </div>
                        @if($order->shipped_at)
                        <div class="detail-row">
                            <span class="detail-label">出貨時間</span>
                            <span class="detail-value">{{ $order->shipped_at->format('Y-m-d H:i:s') }}</span>
                        </div>
                        @endif
                        <div style="margin-top: 1rem;">
                            <button class="btn-action" onclick="queryLogisticsStatus({{ $order->id }})">查詢最新狀態</button>
                        </div>
                    @else
                        <div style="color: #6b7280; margin-bottom: 1rem;">
                            尚未建立物流單
                        </div>
                        @if(in_array($order->status, ['paid', 'processing']))
                            <button class="btn-action btn-primary" onclick="createLogisticsShipment({{ $order->id }}, this)" id="create-shipment-btn">
                                建立物流單
                            </button>
                            <p style="font-size: 0.75rem; color: #9ca3af; margin-top: 0.5rem;">
                                建立後訂單將標記為已完成
                            </p>
                        @else
                            <p style="font-size: 0.875rem; color: #9ca3af;">
                                @if($order->status === 'pending')
                                    請等待客戶完成付款後再建立物流單
                                @else
                                    此訂單狀態無法建立物流單
                                @endif
                            </p>
                        @endif
                    @endif
                </div>

                <div class="order-detail-section">
                    <h3>訂購商品</h3>
                    <div class="order-items-list">
                        @foreach($order->items as $item)
                            <div class="order-item">
                                @if($item->product_image)
                                    <img src="/images/{{ $item->product_image }}" alt="{{ $item->product_name }}" class="order-item-image">
                                @else
                                    <div class="order-item-image"></div>
                                @endif
                                <div class="order-item-info">
                                    <div class="order-item-name">{{ $item->product_name }}</div>
                                    <div class="order-item-price">NT$ {{ number_format($item->price) }} x {{ $item->quantity }}</div>
                                </div>
                                <div style="font-weight: 600;">NT$ {{ number_format($item->subtotal) }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="order-detail-section">
                    <h3>金額明細</h3>
                    <div class="detail-row">
                        <span class="detail-label">小計</span>
                        <span class="detail-value">NT$ {{ number_format($order->subtotal) }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">運費</span>
                        <span class="detail-value">NT$ {{ number_format($order->shipping_fee) }}</span>
                    </div>
                    @if($order->discount > 0)
                    <div class="detail-row">
                        <span class="detail-label">折扣</span>
                        <span class="detail-value">- NT$ {{ number_format($order->discount) }}</span>
                    </div>
                    @endif
                    <div class="detail-row" style="font-size: 1.125rem;">
                        <span class="detail-label" style="font-weight: 600;">總計</span>
                        <span class="detail-value" style="font-weight: 600;">NT$ {{ number_format($order->total) }}</span>
                    </div>
                </div>

                <div class="order-detail-section">
                    <h3>備註</h3>
                    <p style="margin-bottom: 1rem; color: {{ $order->note ? '#111' : '#9ca3af' }};">
                        {{ $order->note ?? '無備註' }}
                    </p>
                    <form action="{{ route('manage-orders.add-note', $order->id) }}" method="POST" class="note-form">
                        @csrf
                        <input type="text" name="note" class="note-input" placeholder="新增或修改備註..." value="{{ $order->note }}">
                        <button type="submit" class="btn-action">儲存備註</button>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <footer class="dashboard-footer">
        <div class="footer-inner">
            <p class="footer-copyright">&copy; 2026 MONO. All rights reserved.</p>
        </div>
    </footer>

</body>
</html>
