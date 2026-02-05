<!DOCTYPE html>
<html lang="zh-TW">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>MONO — 精選生活選物</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@300;400;500;600&family=Noto+Sans+TC:wght@300;400;500&display=swap" rel="stylesheet">

   <!-- 引用 CSS -->
@vite(['resources/css/ecommerce/style.css', 'resources/css/search/style.css'])



  <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>

  <!-- 将后端数据传递给 JavaScript -->
  <script>
    window.categoriesData = @json($categories);
    window.productsData = @json($products);
    // 調試：輸出第一個商品的價格
    console.log('第一個商品數據：', window.productsData[0]);
  </script>

  <!-- 引用 JavaScript -->
  @vite(['resources/js/ecommerce/app.js'])


</head>
<body>
  <div id="app">
    <!-- Header -->
    <header :class="{ scrolled: isScrolled }">
      <div class="header-inner">
        <a href="/" class="logo">MONO</a>
        <nav>
          <a href="{{ route('discount.index') }}">限時優惠</a>
           <a href="{{ route('commodity.index') }}">精選商品</a>
          <a href="{{ route('aboutus.index') }}">關於我們</a>
          <a href="{{ route('brandstory.index') }}">品牌故事</a>
        </nav>
        <div class="header-actions">
          <button class="icon-btn" id="search-btn">
            <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
          </button>

          @auth
          <!-- 訂單查詢 -->
          <a href="{{ route('orders.index') }}" class="icon-btn" title="我的訂單">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
          </a>
          @endauth

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

    <!-- Hero -->
    <section class="hero">
      <div class="hero-content">
        <div class="hero-text">
          <p class="hero-eyebrow">2026 春夏新品</p>
          <h1 class="hero-title">探索生活的<br><em>純粹之美</em></h1>
          <p class="hero-desc">我們相信，真正的美在於簡單。精選世界各地的設計師作品，為您帶來兼具質感與功能的生活選物。</p>
          <div class="hero-actions">
           <a href="{{ route('commodity.index') }}"> <button class="btn btn-primary">探索系列</button></a>
            <a href="{{ route('aboutus.index') }}"><button class="btn btn-secondary">了解更多</button></a>
          </div>
        </div>
        <div class="hero-image">
          <div class="hero-image-wrapper">
            <img src="https://images.unsplash.com/photo-1616486338812-3dadae4b4ace?w=800&q=80" alt="Hero">
          </div>
          <div class="hero-image-accent"></div>
          <div class="hero-stats">
            <div class="hero-stats-number">10</div>
            <div class="hero-stats-label">精選品牌</div>
          </div>
        </div>
      </div>
    </section>

    <!-- Categories -->
    <section class="categories">
      <div class="section-header">
        <h2 class="section-title">精選分類</h2>
        <a href="{{ route('commodity.index') }}"  class="section-link">
          查看全部
          <svg viewBox="0 0 24 24"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
        </a>
      </div>
      <div class="categories-grid">
        <a :href="'{{ route('commodity.index') }}?category=' + encodeURIComponent(category.name)"
           class="category-card"
           v-for="category in categories"
           :key="category.name">
          <div class="category-image">
            <img :src="category.image" :alt="category.name">
          </div>
          <div class="category-info">
            <h3 class="category-name">@{{ category.name }}</h3>
            <p class="category-count">@{{ category.count }} 件商品</p>
          </div>
        </a>
      </div>
    </section>

    <!-- Featured Products -->
    <section class="featured">
      <div class="section-header">
        <h2 class="section-title">人氣商品</h2>
        <a href="{{ route('commodity.index') }}" class="section-link">
          查看全部
          <svg viewBox="0 0 24 24"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
        </a>
      </div>
      <div class="products-grid">
        <div class="product-card" v-for="product in products" :key="product.id">
          <div class="product-image">
            <img :src="product.image" :alt="product.name">
            <span class="product-tag" :class="product.tagType" v-if="product.tag">@{{ product.tag }}</span>
            <div class="product-actions">
              <button class="product-action-btn" @click.stop="addToCart(product)">
                <svg viewBox="0 0 24 24"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
              </button>
              <button class="product-action-btn">
                <svg viewBox="0 0 24 24"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/></svg>
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
    </section>

    <!-- Banner -->
    <section class="banner">
      <div class="banner-inner">
        <div class="banner-content">
          <p class="banner-eyebrow">限時優惠</p>
          <h2 class="banner-title">春季特賣<br>精選商品 9 折起</h2>
          <p class="banner-desc">探索我們精心挑選的春季商品，為您的生活空間注入清新氣息。優惠期間限定，敬請把握。</p>
          <a href="{{ route('discount.index') }}" class="btn-light">立即選購</a>
        </div>
        <div class="banner-image">
          <img src="https://images.unsplash.com/photo-1586023492125-27b2c045efd7?w=800&q=80" alt="Banner">
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
            <a href="#" class="logo">MONO</a>
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
            <img :src="item.image" :alt="item.name">
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
        <button class="cart-checkout" @click="goToCheckout" :disabled="isCheckingOut">
          @{{ isCheckingOut ? '處理中...' : '前往結帳' }}
        </button>
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