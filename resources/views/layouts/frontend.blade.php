<!DOCTYPE html>
<html lang="zh-TW">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'MONO — 精選生活選物')</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@300;400;500;600&family=Noto+Sans+TC:wght@300;400;500&display=swap" rel="stylesheet">

  <!-- 共用 CSS (包含購物車樣式) -->
  @vite(['resources/css/ecommerce/style.css', 'resources/css/search/style.css'])

  <!-- 頁面專屬 CSS -->
  @stack('styles')

  <!-- Vue -->
  <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>

  <!-- 共用購物車模組 -->
  <script src="/js/cart.js"></script>
  <script>
    // 設定當前用戶 ID（用於區分不同用戶的購物車）
    window.CartModule.setUserId(@json(auth()->id()));
  </script>
</head>
<body>
  <div id="app">
    <!-- Header -->
    <header id="header" :class="{ scrolled: isScrolled }">
      <div class="header-inner">
        <a href="/" class="logo">MONO</a>
        <nav>
          <a href="{{ route('discount.index') }}" @if(request()->routeIs('discount.index')) class="active" @endif>限時優惠</a>
          <a href="{{ route('commodity.index') }}" @if(request()->routeIs('commodity.index')) class="active" @endif>精選商品</a>
          <a href="{{ route('aboutus.index') }}" @if(request()->routeIs('aboutus.index')) class="active" @endif>關於我們</a>
          <a href="{{ route('brandstory.index') }}" @if(request()->routeIs('brandstory.index')) class="active" @endif>品牌故事</a>
        </nav>
        <div class="header-actions">
          <button class="icon-btn" id="search-btn">
            <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
          </button>
          @auth
          <a href="{{ route('dashboard') }}" class="icon-btn" title="會員中心">
            <svg viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
          </a>
          @else
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

    <!-- Search Overlay -->
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

    <!-- Page Content -->
    @yield('content')

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
        <button class="cart-checkout" @click="goToCheckout">前往結帳</button>
      </div>
    </div>
  </div>

  <!-- 搜尋功能 JS -->
  @vite(['resources/js/search/app.js'])

  <!-- 頁面 Vue App -->
  <script>
    const { createApp, ref, computed, onMounted, onUnmounted } = Vue

    createApp({
      setup() {
        const isScrolled = ref(false)

        // 使用共用購物車模組
        const { cartItems, cartOpen, cartItemsCount, cartTotal, addToCart, updateQty, clearCart, goToCheckout } = window.CartModule.useCart()

        const handleScroll = () => {
          isScrolled.value = window.scrollY > 50
        }

        onMounted(() => {
          window.addEventListener('scroll', handleScroll)
        })

        onUnmounted(() => {
          window.removeEventListener('scroll', handleScroll)
        })

        return {
          isScrolled,
          cartItems,
          cartOpen,
          cartItemsCount,
          cartTotal,
          addToCart,
          updateQty,
          clearCart,
          goToCheckout
        }
      }
    }).mount('#app')
  </script>

  <!-- 登出時清除購物車 -->
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // 監聽所有登出表單的提交
      document.querySelectorAll('form[action*="logout"]').forEach(function(form) {
        form.addEventListener('submit', function() {
          // 清除當前用戶的購物車
          window.CartModule.forceClearCart();
        });
      });
    });
  </script>

  <!-- 頁面專屬 JS -->
  @stack('scripts')
</body>
</html>
