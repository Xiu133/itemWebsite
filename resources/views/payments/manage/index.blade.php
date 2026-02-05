<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>金流管理 — MONO</title>
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
                <a href="{{ route('merchant.dashboard') }}" class="back-link">
                    <svg viewBox="0 0 24 24"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                    返回賣家中心
                </a>
            </div>

            <div class="revenue-cards">
                <div class="revenue-card highlight">
                    <div class="revenue-label">本月營收</div>
                    <div class="revenue-value">NT$ {{ number_format($statistics['monthly_revenue']) }}</div>
                </div>
                <div class="revenue-card">
                    <div class="revenue-label">上月營收</div>
                    <div class="revenue-value">NT$ {{ number_format($statistics['last_month_revenue']) }}</div>
                </div>
                <div class="revenue-card">
                    <div class="revenue-label">總營收</div>
                    <div class="revenue-value">NT$ {{ number_format($statistics['total_revenue']) }}</div>
                </div>
                <div class="revenue-card">
                    <div class="revenue-label">待付款筆數</div>
                    <div class="revenue-value">{{ $statistics['pending_count'] }}</div>
                </div>
            </div>

            <div class="filter-tabs">
                <a href="{{ route('manage-payments.index') }}" class="filter-tab {{ !$status ? 'active' : '' }}">全部</a>
                <a href="{{ route('manage-payments.index', ['status' => 'pending']) }}" class="filter-tab {{ $status === 'pending' ? 'active' : '' }}">待付款</a>
                <a href="{{ route('manage-payments.index', ['status' => 'paid']) }}" class="filter-tab {{ $status === 'paid' ? 'active' : '' }}">已付款</a>
                <a href="{{ route('manage-payments.index', ['status' => 'failed']) }}" class="filter-tab {{ $status === 'failed' ? 'active' : '' }}">付款失敗</a>
                <a href="{{ route('manage-payments.index', ['status' => 'refunded']) }}" class="filter-tab {{ $status === 'refunded' ? 'active' : '' }}">已退款</a>
            </div>

            <div class="products-section">
                <div class="section-header">
                    <h2 class="section-title">金流管理</h2>
                </div>

                @if($payments->count() > 0)
                    <table class="products-table">
                        <thead>
                            <tr>
                                <th>交易編號</th>
                                <th>訂單編號</th>
                                <th>客戶</th>
                                <th>金額</th>
                                <th>付款方式</th>
                                <th>狀態</th>
                                <th>日期</th>
                                <th>操作</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($payments as $payment)
                                <tr>
                                    <td>
                                        <a href="{{ route('manage-payments.show', $payment->id) }}" style="color: #111; font-weight: 500;">
                                            {{ $payment->trade_no }}
                                        </a>
                                    </td>
                                    <td>{{ $payment->order->order_number }}</td>
                                    <td>{{ $payment->order->shipping_name }}</td>
                                    <td class="payment-amount">NT$ {{ number_format($payment->amount) }}</td>
                                    <td>{{ $payment->payment_method_text ?? '-' }}</td>
                                    <td>
                                        <span class="payment-status payment-{{ $payment->status }}">
                                            {{ $payment->status_text }}
                                        </span>
                                    </td>
                                    <td>{{ $payment->created_at->format('Y-m-d H:i') }}</td>
                                    <td>
                                        <a href="{{ route('manage-payments.show', $payment->id) }}" class="btn-action">查看</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="empty-state">
                        <h3>尚無付款紀錄</h3>
                        <p>目前沒有符合條件的付款紀錄</p>
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
