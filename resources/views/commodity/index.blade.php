<!DOCTYPE html>
<html lang="zh-TW">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>MONO — 精選商品</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@300;400;500;600&family=Noto+Sans+TC:wght@300;400;500&display=swap" rel="stylesheet">

  <!-- 引用 CSS -->
  @vite(['resources/css/commodity/style.css', 'resources/css/search/style.css'])
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>

  <!-- 將後端數據傳遞給 JavaScript -->
  <script>
    window.categoriesData = @json($categories);
    window.brandsData = @json($brands);
    window.productsData = @json($products);
  </script>

  <!-- 引用 JavaScript -->
  @vite(['resources/js/commodity/app.js'])

</head>
<body>
  <div id="app">
    <!-- Header -->
    <header :class="{ scrolled: isScrolled }">
      <div class="header-inner">
        <a href="/" class="logo">MONO</a>
        <nav>
          <a href="{{ route('discount.index') }}">限時優惠</a>
          <a href="{{ route('commodity.index') }}" class="active">精選商品</a>
          <a href="{{ route('aboutus.index') }}">關於我們</a>
          <a href="{{ route('brandstory.index') }}">品牌故事</a>
        </nav>
        <div class="header-actions">
          <button class="icon-btn" id="search-btn">
            <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
          </button>

          @auth
          <!-- 已登入用戶 -->
          <div style="position: relative;">
            <button class="icon-btn" @click="userMenuOpen = !userMenuOpen">
              <svg viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            </button>
            <div v-show="userMenuOpen" class="user-dropdown" @click.stop>
              <div class="user-info">
                <p class="user-name">{{ Auth::user()->name }}</p>
                <p class="user-email">{{ Auth::user()->email }}</p>
              </div>
              <div class="user-menu-divider"></div>
              @if(Auth::user()->role === 'merchant')
              <a href="{{ route('merchant.dashboard') }}" class="user-menu-item">
                <svg viewBox="0 0 24 24"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/></svg>
                賣家中心
              </a>
              @endif
              <a href="{{ route('dashboard') }}" class="user-menu-item">
                <svg viewBox="0 0 24 24"><rect width="7" height="9" x="3" y="3" rx="1"/><rect width="7" height="5" x="14" y="3" rx="1"/><rect width="7" height="9" x="14" y="12" rx="1"/><rect width="7" height="5" x="3" y="16" rx="1"/></svg>
                控制台
              </a>
              <a href="{{ route('profile.show') }}" class="user-menu-item">
                <svg viewBox="0 0 24 24"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                個人資料
              </a>
              <div class="user-menu-divider"></div>
              <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="user-menu-item" style="width: 100%; text-align: left; background: none; border: none; cursor: pointer; font-family: inherit;">
                  <svg viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" x2="9" y1="12" y2="12"/></svg>
                  登出
                </button>
              </form>
            </div>
          </div>
          @else
          <!-- 未登入用戶 -->
          <a href="{{ route('login') }}" class="icon-btn" title="登入">
            <svg viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
          </a>
          @endauth

          <button class="icon-btn" @click="cartOpen = true">
            <svg viewBox="0 0 24 24"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
            <span class="cart-count" v-if="cartItems.length">@{{ cartItemsCount }}</span>
          </button>
        </div>
      </div>
    </header>


    <!-- Page Hero Banner -->
    <section class="page-hero">
      <div class="page-hero-bg">
        <img src="https://images.unsplash.com/photo-1586023492125-27b2c045efd7?w=1920&q=80" alt="精選商品">
      </div>
      <div class="page-hero-overlay"></div>
      <div class="page-hero-content">
        <nav class="breadcrumb">
          <a href="/">首頁</a>
          <span>/</span>
          <span>精選商品</span>
        </nav>
        <p class="page-hero-eyebrow">Featured Products</p>
        <h1 class="page-hero-title">精選商品</h1>
        <p class="page-hero-desc">探索我們精心挑選的設計師作品，為您的生活空間注入獨特美學</p>
      </div>
    </section>

    <!-- Products Section -->
    <section class="products-section">
      <div class="products-container">
        <!-- Sidebar Filters -->
        <aside class="filters-sidebar">
          <div class="filter-group">
            <h3 class="filter-title">商品分類</h3>
            <ul class="filter-list">
              <li>
                <button
                  class="filter-btn"
                  :class="{ active: selectedCategory === null }"
                  @click="selectedCategory = null; filterProducts()"
                >
                  全部商品
                  <span class="filter-count">@{{ products.length }}</span>
                </button>
              </li>
              <li v-for="category in categories" :key="category.id">
                <button
                  class="filter-btn"
                  :class="{ active: selectedCategory === category.id }"
                  @click="selectedCategory = category.id; filterProducts()"
                >
                  @{{ category.name }}
                  <span class="filter-count">@{{ category.count }}</span>
                </button>
              </li>
            </ul>
          </div>

          <div class="filter-group">
            <h3 class="filter-title">價格範圍</h3>
            <ul class="filter-list">
              <li>
                <button
                  class="filter-btn"
                  :class="{ active: priceRange === null }"
                  @click="priceRange = null; filterProducts()"
                >
                  全部價格
                </button>
              </li>
              <li>
                <button
                  class="filter-btn"
                  :class="{ active: priceRange === 'under1000' }"
                  @click="priceRange = 'under1000'; filterProducts()"
                >
                  NT$ 1,000 以下
                </button>
              </li>
              <li>
                <button
                  class="filter-btn"
                  :class="{ active: priceRange === '1000to3000' }"
                  @click="priceRange = '1000to3000'; filterProducts()"
                >
                  NT$ 1,000 - 3,000
                </button>
              </li>
              <li>
                <button
                  class="filter-btn"
                  :class="{ active: priceRange === '3000to5000' }"
                  @click="priceRange = '3000to5000'; filterProducts()"
                >
                  NT$ 3,000 - 5,000
                </button>
              </li>
              <li>
                <button
                  class="filter-btn"
                  :class="{ active: priceRange === 'over5000' }"
                  @click="priceRange = 'over5000'; filterProducts()"
                >
                  NT$ 5,000 以上
                </button>
              </li>
            </ul>
          </div>

          <div class="filter-group">
            <h3 class="filter-title">品牌</h3>
            <ul class="filter-list">
              <li>
                <button
                  class="filter-btn"
                  :class="{ active: selectedBrand === null }"
                  @click="selectedBrand = null; filterProducts()"
                >
                  全部品牌
                </button>
              </li>
              <li v-for="brand in brands" :key="brand.id">
                <button
                  class="filter-btn"
                  :class="{ active: selectedBrand === brand.id }"
                  @click="selectedBrand = brand.id; filterProducts()"
                >
                  @{{ brand.name }}
                  <span class="filter-count">@{{ brand.count }}</span>
                </button>
              </li>
            </ul>
          </div>
        </aside>

        <!-- Products Grid -->
        <div class="products-main">
          <!-- Toolbar -->
          <div class="products-toolbar">
            <p class="products-count">顯示 <strong>@{{ filteredProducts.length }}</strong> 件商品</p>
            <div class="products-sort">
              <label>排序：</label>
              <select v-model="sortBy" @change="sortProducts">
                <option value="newest">最新上架</option>
                <option value="price-low">價格由低到高</option>
                <option value="price-high">價格由高到低</option>
                <option value="name">名稱 A-Z</option>
              </select>
            </div>
          </div>

          <!-- Products Grid -->
          <div class="products-grid">
            <div class="product-card" v-for="product in filteredProducts" :key="product.id">
              <div class="product-image">
                <img :src="'/images/' + product.image" :alt="product.name">
                <span class="product-tag" :class="product.tagType" v-if="product.tag">@{{ product.tag }}</span>
                <div class="product-actions">
                  <button class="product-action-btn" @click.stop="addToCart(product)">
                    <svg viewBox="0 0 24 24"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
                  </button>
                  <button class="product-action-btn" @click.stop="toggleWishlist(product)">
                    <svg viewBox="0 0 24 24" :class="{ filled: isInWishlist(product.id) }"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/></svg>
                  </button>
                </div>
              </div>
              <div class="product-info">
                <p class="product-brand">@{{ product.brand }}</p>
                <h3 class="product-name">@{{ product.name }}</h3>
                <p class="product-price">
                  <span class="original" v-if="product.originalPrice">NT$ @{{ product.originalPrice.toLocaleString() }}</span>
                  <span :class="{ sale: product.originalPrice }">NT$ @{{ product.price.toLocaleString() }}</span>
                </p>
              </div>
            </div>
          </div>

          <!-- Empty State -->
          <div class="empty-state" v-if="filteredProducts.length === 0">
            <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
            <h3>找不到符合條件的商品</h3>
            <p>請嘗試調整篩選條件或搜尋其他關鍵字</p>
            <button class="btn btn-primary" @click="resetFilters">重設篩選</button>
          </div>
        </div>
      </div>
    </section>

    <!-- Newsletter -->
    <section class="newsletter">
      <div class="newsletter-inner">
        <h2 class="newsletter-title">訂閱電子報</h2>
        <p class="newsletter-desc">第一時間收到新品資訊、獨家優惠與生活靈感</p>
        <form class="newsletter-form" @submit.prevent="subscribe">
          <input type="email" class="newsletter-input" placeholder="請輸入您的 Email" v-model="email">
          <button type="submit" class="btn btn-primary">訂閱</button>
        </form>
      </div>
    </section>

    <!-- Footer -->
    <footer>
      <div class="footer-inner">
        <div class="footer-top">
          <div class="footer-brand">
            <a href="/" class="logo">MONO</a>
            <p>精選世界各地的設計師作品，為您帶來兼具質感與功能的生活選物。簡約，卻不簡單。</p>
          </div>
        </div>
        <div class="footer-bottom">
          <p class="footer-copyright">© 2026 MONO. All rights reserved.</p>
          <div class="footer-social">
            <a href="#">
              <svg viewBox="0 0 24 24"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg>
            </a>
            <a href="#">
              <svg viewBox="0 0 24 24"><rect width="20" height="20" x="2" y="2" rx="5" ry="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><path d="M17.5 6.5h.01"/></svg>
            </a>
            <a href="#">
              <svg viewBox="0 0 24 24"><path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6z"/><rect width="4" height="12" x="2" y="9"/><circle cx="4" cy="4" r="2"/></svg>
            </a>
          </div>
        </div>
      </div>
    </footer>

    <!-- Cart Sidebar -->
    <div class="cart-overlay" :class="{ active: cartOpen }" @click="cartOpen = false"></div>
    <div class="cart-sidebar" :class="{ active: cartOpen }">
      <div class="cart-header">
        <h3 class="cart-title">購物車</h3>
        <div style="display: flex; gap: 0.5rem; align-items: center;">
          <button class="cart-clear-btn" @click="clearCart" v-if="cartItems.length" title="清空購物車">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
          </button>
          <button class="cart-close" @click="cartOpen = false">
            <svg viewBox="0 0 24 24" fill="none"><path d="M18 6 6 18M6 6l12 12"/></svg>
          </button>
        </div>
      </div>
      <div class="cart-items">
        <div class="cart-item" v-for="item in cartItems" :key="item.id">
          <div class="cart-item-image">
            <img :src="'/images/' + item.image" :alt="item.name">
          </div>
          <div class="cart-item-info">
            <p class="cart-item-brand">@{{ item.brand }}</p>
            <h4 class="cart-item-name">@{{ item.name }}</h4>
            <p class="cart-item-price">NT$ @{{ item.price.toLocaleString() }}</p>
            <div class="cart-item-qty">
              <button class="qty-btn" @click="updateQty(item, -1)">−</button>
              <span>@{{ item.qty }}</span>
              <button class="qty-btn" @click="updateQty(item, 1)">+</button>
            </div>
          </div>
        </div>
        <p v-if="!cartItems.length" style="text-align: center; color: var(--text-muted); padding: 3rem 0;">購物車是空的</p>
      </div>
      <div class="cart-footer" v-if="cartItems.length">
        <div class="cart-subtotal">
          <span class="cart-subtotal-label">小計</span>
          <span class="cart-subtotal-value">NT$ @{{ cartTotal.toLocaleString() }}</span>
        </div>
        <button class="cart-checkout">前往結帳</button>
      </div>
    </div>
  </div>

  <!-- Search Overlay (放在 Vue app 外面) -->
  <div class="search-overlay" id="search-overlay">
    <div class="search-modal">
      <div class="search-header">
        <div class="search-input-wrapper">
          <svg class="search-icon" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
          <input
            type="text"
            class="search-input"
            id="search-input"
            placeholder="搜尋商品..."
            autocomplete="off"
          >
          <button class="search-clear" id="search-clear" style="display: none;">
            <svg viewBox="0 0 24 24"><path d="M18 6 6 18M6 6l12 12"/></svg>
          </button>
        </div>
        <button class="search-close" id="search-close">
          <svg viewBox="0 0 24 24"><path d="M18 6 6 18M6 6l12 12"/></svg>
        </button>
      </div>
      <div class="search-body">
        <div class="search-loading" id="search-loading" style="display: none;">
          <div class="loading-spinner"></div>
          <span>搜尋中...</span>
        </div>
        <div class="search-results" id="search-results"></div>
        <div class="search-empty" id="search-empty" style="display: none;">
          <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
          <p>找不到符合的商品</p>
          <span>試試其他關鍵字</span>
        </div>
        <div class="search-hint" id="search-hint">
          <p>輸入關鍵字搜尋商品</p>
          <span>支援商品名稱、品牌、分類搜尋</span>
        </div>
      </div>
    </div>
  </div>

  <!-- 引用 Vue -->
  <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>

  <!-- 搜尋功能 JS -->
  @vite(['resources/js/search/app.js'])
</body>
</html>
