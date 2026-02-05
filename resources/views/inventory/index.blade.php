<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>庫存管理 — MONO</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@300;400;500;600&family=Noto+Sans+TC:wght@300;400;500&display=swap" rel="stylesheet">
    @vite(['resources/css/ecommerce/style.css', 'resources/css/merchant/dashboard.css', 'resources/css/product/style.css', 'resources/css/inventory/style.css', 'resources/js/inventory/app.js'])
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

            @if($lowStockCount > 0)
            <div class="alert-warning" style="background: #fef3c7; border: 1px solid #f59e0b; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1.5rem;">
                <strong>低庫存警告：</strong>有 {{ $lowStockCount }} 件商品庫存低於 5 件
            </div>
            @endif

            <div class="products-section">
                <div class="section-header">
                    <h2 class="section-title">庫存管理</h2>
                </div>

                @if($products->count() > 0)
                    <table class="products-table">
                        <thead>
                            <tr>
                                <th>商品</th>
                                <th>目前庫存</th>
                                <th>狀態</th>
                                <th>調整庫存</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($products as $product)
                                <tr class="{{ $product['is_low_stock'] ? 'low-stock-row' : '' }}">
                                    <td>
                                        <div class="product-cell">
                                            @if($product['image'])
                                                <img src="{{ $product['image'] }}" alt="{{ $product['name'] }}" class="product-image">
                                            @else
                                                <div class="product-image"></div>
                                            @endif
                                            <div class="product-info">
                                                <span class="product-name">{{ $product['name'] }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="stock-value {{ $product['is_low_stock'] ? 'stock-warning' : '' }}" id="stock-{{ $product['id'] }}">
                                            {{ $product['stock'] }}
                                        </span>
                                        @if($product['is_low_stock'])
                                            <span class="badge-warning">低庫存</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="status-text {{ $product['is_active'] ? 'status-active' : 'status-inactive' }}">
                                            {{ $product['is_active'] ? '上架中' : '已下架' }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="adjust-controls">
                                            <button class="btn-adjust" onclick="adjustStock({{ $product['id'] }}, -1)">-</button>
                                            <input type="number" id="adjust-{{ $product['id'] }}" value="1" min="1" class="adjust-input">
                                            <button class="btn-adjust" onclick="adjustStock({{ $product['id'] }}, 1)">+</button>
                                            <button class="btn-action" onclick="openAdjustModal({{ $product['id'] }}, '{{ $product['name'] }}', {{ $product['stock'] }})">
                                                手動調整
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="empty-state">
                        <h3>尚無商品</h3>
                        <p>請先新增商品</p>
                    </div>
                @endif
            </div>
        </div>
    </main>

    <div id="adjust-modal" class="modal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3>調整庫存</h3>
                <button class="modal-close" onclick="closeModal()">&times;</button>
            </div>
            <div class="modal-body">
                <p>商品：<strong id="modal-product-name"></strong></p>
                <p>目前庫存：<span id="modal-current-stock"></span></p>
                <div class="form-group">
                    <label>調整數量（正數增加，負數減少）</label>
                    <input type="number" id="modal-change" class="form-control">
                </div>
                <div class="form-group">
                    <label>調整原因</label>
                    <input type="text" id="modal-reason" class="form-control" placeholder="例：盤點調整、進貨補充">
                </div>
                <input type="hidden" id="modal-product-id">
            </div>
            <div class="modal-footer">
                <button class="btn-secondary" onclick="closeModal()">取消</button>
                <button class="btn-primary" onclick="submitAdjust()">確認調整</button>
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
