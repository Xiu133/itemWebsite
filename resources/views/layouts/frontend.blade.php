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

  <!-- 搜尋功能 CSS -->
  @vite(['resources/css/search/style.css'])

  <!-- 頁面專屬 CSS -->
  @stack('styles')
</head>
<body>
  <!-- Header -->
  <header id="header">
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
        <button class="icon-btn">
          <svg viewBox="0 0 24 24"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
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

  <!-- 搜尋功能 JS (包含 Header 滾動效果) -->
  @vite(['resources/js/search/app.js'])

  <!-- 頁面專屬 JS -->
  @stack('scripts')
</body>
</html>
