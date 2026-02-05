<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>賣家中心 — MONO</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@300;400;500;600&family=Noto+Sans+TC:wght@300;400;500&display=swap" rel="stylesheet">
    @vite(['resources/css/ecommerce/style.css', 'resources/css/merchant/dashboard.css', 'resources/js/merchant/dashboard.js'])
</head>
<body>
    <!-- Header -->
    <header>
        <div class="header-inner">
            <a href="/" class="logo">MONO</a>
            <nav class="merchant-nav">
                <a href="{{ route('merchant.dashboard') }}" class="active">賣家中心</a>
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
            <!-- Welcome Section -->
            <div class="welcome-section">
                <div class="welcome-text">
                    <h1 class="welcome-title">歡迎回來，{{ auth()->user()->name }}</h1>
                    <p class="welcome-subtitle">管理您的商店與商品</p>
                </div>
                <div class="welcome-date">
                    {{ now()->format('Y年m月d日') }}
                </div>
            </div>

            <!-- Management Cards -->
            <div class="management-grid">
                <!-- 商品管理 -->
                <div class="management-card expandable" id="product-card">
                    <div class="card-main" onclick="toggleCard('product-card')">
                        <div class="card-icon">
                            <svg viewBox="0 0 24 24">
                                <path d="M12 2L2 7l10 5 10-5-10-5z"/>
                                <path d="M2 17l10 5 10-5"/>
                                <path d="M2 12l10 5 10-5"/>
                            </svg>
                        </div>
                        <div class="card-content">
                            <h3 class="card-title">商品管理</h3>
                            <p class="card-desc">上架、編輯、管理您的所有商品</p>
                        </div>
                        <div class="card-arrow">
                            <svg viewBox="0 0 24 24" class="expand-icon"><path d="M6 9l6 6 6-6"/></svg>
                        </div>
                    </div>
                    <div class="card-submenu">
                        <a href="{{ route('my-products.index') }}" class="submenu-item">
                            <svg viewBox="0 0 24 24"><path d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"/></svg>
                            商品列表
                        </a>
                        <a href="{{ route('my-products.create') }}" class="submenu-item">
                            <svg viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
                            新增商品
                        </a>
                    </div>
                </div>

                <!-- 庫存管理 -->
                <a href="{{ route('inventory.index') }}" class="management-card">
                    <div class="card-icon">
                        <svg viewBox="0 0 24 24">
                            <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
                            <polyline points="3.27 6.96 12 12.01 20.73 6.96"/>
                            <line x1="12" y1="22.08" x2="12" y2="12"/>
                        </svg>
                    </div>
                    <div class="card-content">
                        <h3 class="card-title">庫存管理</h3>
                        <p class="card-desc">追蹤庫存數量、設定低庫存提醒</p>
                    </div>
                    <div class="card-arrow">
                        <svg viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                    </div>
                </a>

                <!-- 訂單管理 -->
                <a href="{{ route('manage-orders.index') }}" class="management-card">
                    <div class="card-icon">
                        <svg viewBox="0 0 24 24">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                            <polyline points="14 2 14 8 20 8"/>
                            <line x1="16" y1="13" x2="8" y2="13"/>
                            <line x1="16" y1="17" x2="8" y2="17"/>
                            <polyline points="10 9 9 9 8 9"/>
                        </svg>
                    </div>
                    <div class="card-content">
                        <h3 class="card-title">訂單管理</h3>
                        <p class="card-desc">查看訂單狀態、處理出貨事宜</p>
                    </div>
                    <div class="card-arrow">
                        <svg viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                    </div>
                </a>

                <!-- 金流管理 -->
                <a href="{{ route('manage-payments.index') }}" class="management-card">
                    <div class="card-icon">
                        <svg viewBox="0 0 24 24">
                            <line x1="12" y1="1" x2="12" y2="23"/>
                            <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                        </svg>
                    </div>
                    <div class="card-content">
                        <h3 class="card-title">金流管理</h3>
                        <p class="card-desc">查看營收報表、管理收款帳戶</p>
                    </div>
                    <div class="card-arrow">
                        <svg viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                    </div>
                </a>

                <!-- 物流管理 -->
                <a href="{{ route('manage-logistics.index') }}" class="management-card">
                    <div class="card-icon">
                        <svg viewBox="0 0 24 24">
                            <rect x="1" y="3" width="15" height="13"/>
                            <polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/>
                            <circle cx="5.5" cy="18.5" r="2.5"/>
                            <circle cx="18.5" cy="18.5" r="2.5"/>
                        </svg>
                    </div>
                    <div class="card-content">
                        <h3 class="card-title">物流管理</h3>
                        <p class="card-desc">建立物流單、追蹤配送狀態</p>
                    </div>
                    <div class="card-arrow">
                        <svg viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                    </div>
                </a>
            </div>

            <!-- Quick Stats -->
            <div class="stats-section">
                <h2 class="stats-title">快速概覽</h2>
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-value">{{ $stats['total_products'] }}</div>
                        <div class="stat-label">商品總數</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value">{{ $stats['pending_orders'] }}</div>
                        <div class="stat-label">待處理訂單</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value">NT$ {{ number_format($stats['monthly_revenue']) }}</div>
                        <div class="stat-label">本月營收</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value {{ $stats['low_stock_products'] > 0 ? 'text-warning' : '' }}">{{ $stats['low_stock_products'] }}</div>
                        <div class="stat-label">低庫存商品</div>
                    </div>
                </div>
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
