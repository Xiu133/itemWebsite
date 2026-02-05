<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>商品管理 — MONO</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@300;400;500;600&family=Noto+Sans+TC:wght@300;400;500&display=swap" rel="stylesheet">
    @vite(['resources/css/ecommerce/style.css', 'resources/css/merchant/dashboard.css', 'resources/css/product/style.css', 'resources/js/product/app.js'])
</head>
<body>
    <!-- Header -->
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

    <!-- Main Content -->
    <main class="dashboard-page">
        <div class="dashboard-container">
            <!-- Breadcrumb -->
            <div style="margin-bottom: 1.5rem;">
                <a href="{{ route('merchant.dashboard') }}" class="back-link">
                    <svg viewBox="0 0 24 24"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                    返回賣家中心
                </a>
            </div>

            @if(session('success'))
                <div class="success-message" style="margin-bottom: 1.5rem;">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Products Section -->
            <div class="products-section">
                <div class="section-header">
                    <h2 class="section-title">商品列表</h2>
                    <a href="{{ route('my-products.create') }}" class="btn-add">
                        <svg viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
                        新增商品
                    </a>
                </div>

                @if($products->count() > 0)
                    <table class="products-table">
                        <thead>
                            <tr>
                                <th>商品</th>
                                <th>價格</th>
                                <th>庫存</th>
                                <th>狀態</th>
                                <th>上架日期</th>
                                <th>操作</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($products as $product)
                                <tr>
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
                                    <td>NT$ {{ number_format($product['price']) }}</td>
                                    <td class="{{ $product['stock'] <= 5 ? 'stock-warning' : '' }}">
                                        {{ $product['stock'] }}
                                        @if($product['stock'] <= 5)
                                            <span style="font-size: 0.75rem;">（低庫存）</span>
                                        @endif
                                    </td>
                                    <td>
                                        <label class="toggle-switch">
                                            <input type="checkbox"
                                                   class="toggle-status"
                                                   data-product-id="{{ $product['id'] }}"
                                                   {{ $product['is_active'] ? 'checked' : '' }}>
                                            <span class="toggle-slider"></span>
                                        </label>
                                        <span class="status-text {{ $product['is_active'] ? 'status-active' : 'status-inactive' }}">
                                            {{ $product['is_active'] ? '上架中' : '已下架' }}
                                        </span>
                                    </td>
                                    <td>{{ $product['created_at'] }}</td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="{{ route('my-products.edit', $product['id']) }}" class="btn-action">編輯</a>
                                            <form action="{{ route('my-products.destroy', $product['id']) }}" method="POST" style="display: inline;" onsubmit="return confirm('確定要刪除此商品嗎？')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn-action btn-delete">刪除</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="empty-state">
                        <svg viewBox="0 0 24 24"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/></svg>
                        <h3>尚無商品</h3>
                        <p>開始新增您的第一個商品吧！</p>
                        <a href="{{ route('my-products.create') }}" class="btn-add">
                            <svg viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
                            新增商品
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="dashboard-footer">
        <div class="footer-inner">
            <p class="footer-copyright">&copy; 2026 MONO. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>
