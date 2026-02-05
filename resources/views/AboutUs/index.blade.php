@extends('layouts.frontend')

@section('title', '關於我們 — MONO 精選生活選物')

@push('styles')
  @vite(['resources/css/aboutus/style.css'])
@endpush

@section('content')
  <!-- Page Hero Banner -->
  <section class="page-hero">
    <div class="page-hero-bg">
      <img src="https://images.unsplash.com/photo-1618221195710-dd6b41faaea6?w=1920&q=80" alt="關於我們">
    </div>
    <div class="page-hero-overlay"></div>
    <div class="page-hero-content">
      <p class="page-hero-eyebrow">About Us</p>
      <h1 class="page-hero-title">關於我們</h1>
      <p class="page-hero-desc">探索 MONO 的故事與理念</p>
    </div>
  </section>

  <!-- Brand Story Section -->
  <section class="about-section">
    <div class="about-inner">
      <div class="about-image">
        <img src="https://images.unsplash.com/photo-1616486338812-3dadae4b4ace?w=800&q=80" alt="MONO 的誕生">
        <div class="about-image-accent"></div>
      </div>
      <div class="about-content">
        <p class="about-eyebrow">Our Story</p>
        <h2 class="about-title">MONO 的誕生</h2>
        <p class="about-desc">
          MONO 創立於 2020 年，源自一個簡單的信念：生活中的每一件物品，都應該帶來美好與愉悅。我們的名字取自「Monotone」，象徵著對純粹與本質的追求。
        </p>
        <p class="about-desc">
          我們走訪世界各地，與獨立設計師和工匠合作，嚴選每一件商品。從北歐的極簡傢俱、日本的職人餐具，到台灣在地的手作織品，每一件選物都承載著創作者的心意與故事。
        </p>
      </div>
    </div>
  </section>

  <!-- Philosophy Section -->
  <section class="philosophy-section">
    <div class="philosophy-inner">
      <div class="philosophy-content">
        <p class="philosophy-eyebrow">Our Philosophy</p>
        <h2 class="philosophy-title">簡約，<em>卻不簡單</em></h2>
        <p class="philosophy-desc">
          我們相信，好的設計不只是外在的美麗，更是使用時的舒適與愉悅。每一件 MONO 選物，都經過嚴格的品質把關與生活實測，確保能真正融入您的日常。
        </p>
      </div>
    </div>
  </section>

  <!-- Values Section -->
  <section class="values-section">
    <div class="values-inner">
      <div class="section-header">
        <p class="section-eyebrow">Our Values</p>
        <h2 class="section-title">我們的核心價值</h2>
      </div>
      <div class="values-grid">
        <div class="value-card">
          <div class="value-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
              <path d="M12 2L2 7l10 5 10-5-10-5z"/>
              <path d="M2 17l10 5 10-5"/>
              <path d="M2 12l10 5 10-5"/>
            </svg>
          </div>
          <h3 class="value-title">精心選品</h3>
          <p class="value-desc">每件商品皆經過層層篩選，確保品質與設計兼具。我們親自拜訪工坊，了解每件作品背後的故事。</p>
        </div>
        <div class="value-card">
          <div class="value-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
              <circle cx="12" cy="12" r="10"/>
              <path d="M12 6v6l4 2"/>
            </svg>
          </div>
          <h3 class="value-title">永續理念</h3>
          <p class="value-desc">選擇經得起時間考驗的設計，減少不必要的浪費。我們支持環保材質與在地製造。</p>
        </div>
        <div class="value-card">
          <div class="value-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
              <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
            </svg>
          </div>
          <h3 class="value-title">用心服務</h3>
          <p class="value-desc">專業團隊為您提供貼心的購物體驗與售後支援。您的滿意是我們最大的動力。</p>
        </div>
        <div class="value-card">
          <div class="value-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
              <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
              <circle cx="9" cy="7" r="4"/>
              <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
              <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
            </svg>
          </div>
          <h3 class="value-title">社群連結</h3>
          <p class="value-desc">我們不只賣商品，更建立一個愛好生活美學的社群。定期舉辦工作坊與分享會。</p>
        </div>
      </div>
    </div>
  </section>

  <!-- Team Section -->
  <section class="team-section">
    <div class="team-inner">
      <div class="team-content">
        <div class="team-image">
          <img src="https://images.unsplash.com/photo-1600880292203-757bb62b4baf?w=800&q=80" alt="我們的團隊">
        </div>
        <div class="team-text">
          <p class="team-eyebrow">Our Team</p>
          <h2 class="team-title">熱愛生活的團隊</h2>
          <p class="team-desc">
            MONO 的團隊由一群熱愛設計、崇尚簡約生活的夥伴組成。我們來自不同領域——有建築師、室內設計師、藝術家，也有曾在國際品牌工作的行銷人。
          </p>
          <p class="team-desc">
            共同的信念將我們聚在一起：希望透過精選的生活選物，讓更多人感受到日常中的美好。
          </p>
        </div>
      </div>
    </div>
  </section>

  <!-- CTA Section -->
  <section class="cta-section">
    <div class="cta-inner">
      <h2 class="cta-title">探索我們的精選商品</h2>
      <p class="cta-desc">發現為您的生活空間帶來美好與質感的選物</p>
      <a href="/" class="btn btn-primary">前往選購</a>
    </div>
  </section>
@endsection
