<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>付款詳情 — MONO</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@300;400;500;600&family=Noto+Sans+TC:wght@300;400;500&display=swap" rel="stylesheet">
    @vite(['resources/css/ecommerce/style.css', 'resources/css/merchant/dashboard.css', 'resources/css/product/style.css', 'resources/css/payments/manage.css', 'resources/js/payments/manage.js'])
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
                <a href="{{ route('manage-payments.index') }}" class="back-link">
                    <svg viewBox="0 0 24 24"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                    返回金流列表
                </a>
            </div>

            @if(session('success'))
                <div class="success-message" style="margin-bottom: 1.5rem;">
                    {{ session('success') }}
                </div>
            @endif

            <div class="products-section">
                <div class="section-header">
                    <h2 class="section-title">付款詳情</h2>
                    <span class="payment-status payment-{{ $payment->status }}">{{ $payment->status_text }}</span>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-top: 1.5rem;">
                    <div class="payment-detail-section">
                        <h3>交易資訊</h3>
                        <div class="detail-grid">
                            <div class="detail-item">
                                <div class="detail-item-label">商店交易編號</div>
                                <div class="detail-item-value">{{ $payment->trade_no }}</div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-item-label">綠界交易編號</div>
                                <div class="detail-item-value">{{ $payment->ecpay_trade_no ?? '-' }}</div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-item-label">付款金額</div>
                                <div class="detail-item-value" style="font-size: 1.25rem; color: #111;">NT$ {{ number_format($payment->amount) }}</div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-item-label">付款方式</div>
                                <div class="detail-item-value">{{ $payment->payment_method_text ?? '-' }}</div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-item-label">建立時間</div>
                                <div class="detail-item-value">{{ $payment->created_at->format('Y-m-d H:i:s') }}</div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-item-label">付款時間</div>
                                <div class="detail-item-value">{{ $payment->payment_date ? $payment->payment_date->format('Y-m-d H:i:s') : '-' }}</div>
                            </div>
                        </div>

                        @if($payment->status === 'paid')
                        <div class="refund-form">
                            <h4>退款處理</h4>
                            <form action="{{ route('manage-payments.refund', $payment->id) }}" method="POST" onsubmit="return confirm('確定要執行退款嗎？此操作無法復原。')">
                                @csrf
                                <input type="text" name="reason" class="refund-input" placeholder="退款原因（選填）">
                                <button type="submit" class="btn-refund">執行退款</button>
                            </form>
                        </div>
                        @endif
                    </div>

                    <div class="payment-detail-section">
                        <h3>關聯訂單</h3>
                        <div class="detail-grid">
                            <div class="detail-item">
                                <div class="detail-item-label">訂單編號</div>
                                <div class="detail-item-value">
                                    <a href="{{ route('manage-orders.show', $payment->order->id) }}" style="color: #111;">
                                        {{ $payment->order->order_number }}
                                    </a>
                                </div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-item-label">訂單狀態</div>
                                <div class="detail-item-value">{{ $payment->order->status_text }}</div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-item-label">客戶姓名</div>
                                <div class="detail-item-value">{{ $payment->order->shipping_name }}</div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-item-label">客戶電話</div>
                                <div class="detail-item-value">{{ $payment->order->shipping_phone }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                @if($payment->response_data)
                <div class="payment-detail-section">
                    <h3>綠界回傳資料</h3>
                    <pre style="background: #f9fafb; padding: 1rem; border-radius: 0.375rem; font-size: 0.875rem; overflow-x: auto;">{{ json_encode($payment->response_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                </div>
                @endif
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
