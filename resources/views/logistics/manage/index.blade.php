<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>物流管理 — MONO</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@300;400;500;600&family=Noto+Sans+TC:wght@300;400;500&display=swap" rel="stylesheet">
    @vite(['resources/css/ecommerce/style.css', 'resources/css/merchant/dashboard.css', 'resources/css/product/style.css', 'resources/css/orders/manage.css', 'resources/js/logistics/manage.js', 'resources/css/delivery/style.css'])
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
                    <div class="stat-number">{{ $stats['pending_shipment'] }}</div>
                    <div class="stat-label">待出貨</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">{{ $stats['in_transit'] }}</div>
                    <div class="stat-label">運送中</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">{{ $stats['delivered'] }}</div>
                    <div class="stat-label">已送達</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">{{ $stats['total_shipped'] }}</div>
                    <div class="stat-label">已建立物流單</div>
                </div>
            </div>

            <div class="filter-tabs">
                <a href="{{ route('manage-logistics.index') }}" class="filter-tab {{ !request('logistics_status') ? 'active' : '' }}">全部</a>
                <a href="{{ route('manage-logistics.index', ['logistics_status' => 'pending']) }}" class="filter-tab {{ request('logistics_status') === 'pending' ? 'active' : '' }}">待出貨</a>
                <a href="{{ route('manage-logistics.index', ['logistics_status' => 'created']) }}" class="filter-tab {{ request('logistics_status') === 'created' ? 'active' : '' }}">已建立</a>
                <a href="{{ route('manage-logistics.index', ['logistics_status' => 'in_transit']) }}" class="filter-tab {{ request('logistics_status') === 'in_transit' ? 'active' : '' }}">運送中</a>
                <a href="{{ route('manage-logistics.index', ['logistics_status' => 'delivered']) }}" class="filter-tab {{ request('logistics_status') === 'delivered' ? 'active' : '' }}">已送達</a>
            </div>

            <div class="products-section">
                <div class="section-header" style="display: flex; justify-content: space-between; align-items: center;">
                    <h2 class="section-title">物流管理</h2>
                    <div class="batch-actions" id="batch-actions" style="display: flex; align-items: center; gap: 1rem;">
                        <span id="selected-count" style="color: #6b7280; font-size: 0.875rem;">已選擇 0 筆</span>
                        <button class="btn-action btn-primary" onclick="batchCreateShipment()" id="batch-create-btn" disabled style="opacity: 0.5; cursor: not-allowed;">
                            批次出貨
                        </button>
                    </div>
                </div>

                @if($orders->count() > 0)
                    <table class="products-table">
                        <thead>
                            <tr>
                                <th style="width: 40px;">
                                    <input type="checkbox" id="select-all" onchange="toggleSelectAll(this)">
                                </th>
                                <th>訂單編號</th>
                                <th>收件人</th>
                                <th>收件地址</th>
                                <th>物流狀態</th>
                                <th>物流編號</th>
                                <th>操作</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($orders as $order)
                                @php
                                    // 可以建立物流單的條件
                                    $canCreateShipment = !$order->logistics_trade_no && (
                                        in_array($order->status, ['paid', 'processing']) ||
                                        ($order->payment_method === 'cash_on_delivery' && $order->status === 'pending')
                                    );
                                @endphp
                                <tr data-order-id="{{ $order->id }}" data-can-ship="{{ $canCreateShipment ? '1' : '0' }}">
                                    <td>
                                        @if($canCreateShipment)
                                            <input type="checkbox" class="order-checkbox" value="{{ $order->id }}" onchange="updateSelectedCount()">
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('manage-orders.show', $order->id) }}" style="color: #111; font-weight: 500;">
                                            {{ $order->order_number }}
                                        </a>
                                        <div style="font-size: 0.75rem; color: #6b7280;">
                                            NT$ {{ number_format($order->total) }}
                                        </div>
                                    </td>
                                    <td>
                                        <div>{{ $order->shipping_name }}</div>
                                        <div style="font-size: 0.75rem; color: #6b7280;">{{ $order->shipping_phone }}</div>
                                    </td>
                                    <td style="max-width: 200px;">
                                        <div style="font-size: 0.875rem;">
                                            {{ $order->shipping_city }}{{ $order->shipping_district }}
                                        </div>
                                        <div style="font-size: 0.75rem; color: #6b7280;">
                                            {{ $order->shipping_address }}
                                        </div>
                                    </td>
                                    <td>
                                        @if($order->logistics_trade_no)
                                            <span class="order-status status-{{ $order->logistics_status ?? 'created' }}">
                                                {{ $order->logistics_status_text }}
                                            </span>
                                        @else
                                            <span class="order-status status-pending">待建立物流單</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($order->all_pay_logistics_id)
                                            <div style="font-size: 0.875rem; font-family: monospace;">
                                                {{ $order->all_pay_logistics_id }}
                                            </div>
                                            <div style="font-size: 0.75rem; color: #6b7280;">
                                                {{ $order->logistics_sub_type_text }}
                                            </div>
                                        @else
                                            <span style="color: #9ca3af;">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($canCreateShipment)
                                            <button class="btn-action btn-primary" onclick="createShipment({{ $order->id }}, this)">
                                                建立物流單
                                            </button>
                                        @elseif($order->logistics_trade_no)
                                            <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                                                <select class="status-select" id="status-select-{{ $order->id }}" style="padding: 0.25rem 0.5rem; font-size: 0.75rem; border: 1px solid #d1d5db; border-radius: 4px;">
                                                    <option value="created" {{ $order->logistics_status === 'created' ? 'selected' : '' }}>已建立</option>
                                                    <option value="picked_up" {{ $order->logistics_status === 'picked_up' ? 'selected' : '' }}>已取件</option>
                                                    <option value="in_transit" {{ $order->logistics_status === 'in_transit' ? 'selected' : '' }}>運送中</option>
                                                    <option value="delivered" {{ $order->logistics_status === 'delivered' ? 'selected' : '' }}>已送達</option>
                                                </select>
                                                <button class="btn-action" onclick="updateStatus({{ $order->id }})" style="padding: 0.25rem 0.5rem; font-size: 0.75rem;">
                                                    更新
                                                </button>
                                            </div>
                                        @else
                                            <span style="color: #9ca3af; font-size: 0.875rem;">
                                                {{ $order->status === 'pending' ? '待付款' : '-' }}
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <div style="margin-top: 1.5rem;">
                        {{ $orders->links() }}
                    </div>
                @else
                    <div class="empty-state">
                        <h3>尚無訂單</h3>
                        <p>目前沒有需要處理物流的訂單</p>
                    </div>
                @endif
            </div>
        </div>
    </main>

    <!-- 物流狀態彈窗 -->
    <div id="status-modal" class="modal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3>物流狀態</h3>
                <button class="modal-close" onclick="closeModal()">&times;</button>
            </div>
            <div class="modal-body" id="status-content">
                載入中...
            </div>
        </div>
    </div>

    <footer class="dashboard-footer">
        <div class="footer-inner">
            <p class="footer-copyright">&copy; 2026 MONO. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>
