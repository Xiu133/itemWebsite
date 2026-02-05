<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>刪除商品 — MONO</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@300;400;500;600&family=Noto+Sans+TC:wght@300;400;500&display=swap" rel="stylesheet">
    @vite(['resources/css/ecommerce/style.css', 'resources/css/merchant/dashboard.css', 'resources/css/product/delete.css', 'resources/js/product/delete.js'])
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
                                <svg viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1-2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
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
        <div class="dashboard-container" style="max-width: 600px;">
            <!-- Breadcrumb -->
            <div style="margin-bottom: 1.5rem;">
                <a href="{{ route('my-products.index') }}" class="back-link">
                    <svg viewBox="0 0 24 24"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                    返回商品管理
                </a>
            </div>

            <!-- Delete Confirmation Section -->
            <div class="delete-section">
                <div class="delete-header">
                    <div class="delete-icon">
                        <svg viewBox="0 0 24 24"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
                    </div>
                    <h2 class="delete-title">確認刪除商品</h2>
                    <p class="delete-subtitle">此操作將把商品移至回收站</p>
                </div>

                <div class="product-preview">
                    @if($product->image)
                        <div class="product-image">
                            <img src="/images/{{ $product->image }}" alt="{{ $product->name }}">
                        </div>
                    @endif
                    <div class="product-info">
                        <h3 class="product-name">{{ $product->name }}</h3>
                        <div class="product-details">
                            <span class="detail-item">
                                <svg viewBox="0 0 24 24"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>
                                {{ $product->category->name ?? '未分類' }}
                            </span>
                            <span class="detail-item">
                                <svg viewBox="0 0 24 24"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                                NT$ {{ number_format($product->price) }}
                            </span>
                            <span class="detail-item">
                                <svg viewBox="0 0 24 24"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>
                                庫存 {{ $product->stock }}
                            </span>
                        </div>
                        <div class="product-status {{ $product->is_active ? 'active' : 'inactive' }}">
                            {{ $product->is_active ? '上架中' : '已下架' }}
                        </div>
                    </div>
                </div>

                <div class="warning-box">
                    <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    <div class="warning-content">
                        <p class="warning-title">注意事項</p>
                        <ul class="warning-list">
                            <li>商品將被移至回收站，不會立即永久刪除</li>
                            <li>刪除後商品將從商店中下架</li>
                            <li>如需恢復，請聯繫客服人員</li>
                        </ul>
                    </div>
                </div>

                <form action="{{ route('my-products.destroy', $product->id) }}" method="POST" class="delete-form">
                    @csrf
                    @method('DELETE')

                    <div class="confirm-checkbox">
                        <input type="checkbox" id="confirm-delete" required>
                        <label for="confirm-delete">我確認要刪除此商品</label>
                    </div>

                    <div class="form-actions">
                        <a href="{{ route('my-products.index') }}" class="btn-cancel">取消</a>
                        <button type="submit" class="btn-delete" id="btn-delete" disabled>
                            <svg viewBox="0 0 24 24"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
                            確認刪除
                        </button>
                    </div>
                </form>
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
