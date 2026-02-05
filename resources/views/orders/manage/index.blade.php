<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>訂單管理 — MONO</title>
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
                <a href="{{ route('merchant.dashboard') }}" class="back-link">
                    <svg viewBox="0 0 24 24"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                    返回賣家中心
                </a>
            </div>

            <div class="stats-bar">
                <div class="stat-item">
                    <div class="stat-number">{{ $statistics['pending'] }}</div>
                    <div class="stat-label">待付款</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">{{ $statistics['paid'] }}</div>
                    <div class="stat-label">已付款</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">{{ $statistics['processing'] }}</div>
                    <div class="stat-label">處理中</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">{{ $statistics['shipped'] }}</div>
                    <div class="stat-label">已出貨</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">{{ $statistics['completed'] }}</div>
                    <div class="stat-label">已完成</div>
                </div>
            </div>

            <div class="filter-tabs">
                <a href="{{ route('manage-orders.index') }}" class="filter-tab {{ !$status ? 'active' : '' }}">全部</a>
                <a href="{{ route('manage-orders.index', ['status' => 'pending']) }}" class="filter-tab {{ $status === 'pending' ? 'active' : '' }}">待付款</a>
                <a href="{{ route('manage-orders.index', ['status' => 'paid']) }}" class="filter-tab {{ $status === 'paid' ? 'active' : '' }}">已付款</a>
                <a href="{{ route('manage-orders.index', ['status' => 'processing']) }}" class="filter-tab {{ $status === 'processing' ? 'active' : '' }}">處理中</a>
                <a href="{{ route('manage-orders.index', ['status' => 'shipped']) }}" class="filter-tab {{ $status === 'shipped' ? 'active' : '' }}">已出貨</a>
                <a href="{{ route('manage-orders.index', ['status' => 'completed']) }}" class="filter-tab {{ $status === 'completed' ? 'active' : '' }}">已完成</a>
                <a href="{{ route('manage-orders.index', ['status' => 'cancelled']) }}" class="filter-tab {{ $status === 'cancelled' ? 'active' : '' }}">已取消</a>
            </div>

            <div class="products-section">
                <div class="section-header">
                    <h2 class="section-title">訂單管理</h2>
                </div>

                @if($orders->count() > 0)
                    <table class="products-table">
                        <thead>
                            <tr>
                                <th>訂單編號</th>
                                <th>客戶</th>
                                <th>金額</th>
                                <th>狀態</th>
                                <th>日期</th>
                                <th>操作</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($orders as $order)
                                <tr>
                                    <td>
                                        <a href="{{ route('manage-orders.show', $order->id) }}" style="color: #111; font-weight: 500;">
                                            {{ $order->order_number }}
                                        </a>
                                    </td>
                                    <td>{{ $order->shipping_name }}</td>
                                    <td class="order-total">NT$ {{ number_format($order->total) }}</td>
                                    <td>
                                        <span class="order-status status-{{ $order->status }}">
                                            {{ $order->status_text }}
                                        </span>
                                    </td>
                                    <td>{{ $order->created_at->format('Y-m-d H:i') }}</td>
                                    <td>
                                        <select class="status-select" onchange="updateOrderStatus({{ $order->id }}, this)">
                                            <option value="pending" {{ $order->status === 'pending' ? 'selected' : '' }}>待付款</option>
                                            <option value="paid" {{ $order->status === 'paid' ? 'selected' : '' }}>已付款</option>
                                            <option value="processing" {{ $order->status === 'processing' ? 'selected' : '' }}>處理中</option>
                                            <option value="shipped" {{ $order->status === 'shipped' ? 'selected' : '' }}>已出貨</option>
                                            <option value="completed" {{ $order->status === 'completed' ? 'selected' : '' }}>已完成</option>
                                            <option value="cancelled" {{ $order->status === 'cancelled' ? 'selected' : '' }}>已取消</option>
                                        </select>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="empty-state">
                        <h3>尚無訂單</h3>
                        <p>目前沒有符合條件的訂單</p>
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
