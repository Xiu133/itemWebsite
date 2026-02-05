@extends('layouts.frontend')

@section('title', '品牌故事 — MONO 精選生活選物')

@push('styles')
  @vite(['resources/css/brandstory/style.css'])
@endpush

@section('content')
  <!-- Page Hero Banner -->
  <section class="page-hero">
    <div class="page-hero-bg">
      <img src="https://images.unsplash.com/photo-1493663284031-b7e3aefcae8e?w=1920&q=80" alt="品牌故事">
    </div>
    <div class="page-hero-overlay"></div>
    <div class="page-hero-content">
      <p class="page-hero-eyebrow">Brand Story</p>
      <h1 class="page-hero-title">品牌故事</h1>
      <p class="page-hero-desc">簡約，卻不簡單</p>
    </div>
  </section>

  <!-- Origin Section -->
  <section class="origin-section">
    <div class="origin-inner">
      <div class="origin-content">
        <p class="origin-eyebrow">The Beginning</p>
        <h2 class="origin-title">一切的起點</h2>
        <p class="origin-desc">
          2020 年的某個午後，創辦人在東京一家小小的選物店裡，被一只手工陶杯深深吸引。那只杯子沒有華麗的裝飾，卻散發著溫潤的光澤，握在手中的觸感讓人感到無比安心。
        </p>
        <p class="origin-desc">
          那一刻，他明白了：真正的美，不在於繁複的設計，而在於對本質的尊重。這就是 MONO 誕生的起點。
        </p>
      </div>
      <div class="origin-image">
        <img src="https://images.unsplash.com/photo-1565193566173-7a0ee3dbe261?w=800&q=80" alt="品牌起源">
        <div class="origin-image-accent"></div>
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
          MONO 的名字源自「Monotone」——單一色調。我們相信，當一切回歸本質，美便自然顯現。每一件選物都經過層層把關，只為呈現最純粹的設計之美。
        </p>
      </div>
    </div>
  </section>

  <!-- Journey Section -->
  <section class="journey-section">
    <div class="journey-inner">
      <div class="section-header">
        <p class="section-eyebrow">Our Journey</p>
        <h2 class="section-title">品牌旅程</h2>
      </div>
      <div class="timeline">
        <div class="timeline-item">
          <div class="timeline-year">2020</div>
          <div class="timeline-content">
            <h3 class="timeline-title">品牌創立</h3>
            <p class="timeline-desc">MONO 在台北正式成立，以線上選物店的形式開始營運，精選來自世界各地的生活好物。</p>
          </div>
        </div>
        <div class="timeline-item">
          <div class="timeline-year">2021</div>
          <div class="timeline-content">
            <h3 class="timeline-title">首間實體店</h3>
            <p class="timeline-desc">在台北大安區開設首間實體概念店，讓顧客能親身體驗每件選物的質感與故事。</p>
          </div>
        </div>
        <div class="timeline-item">
          <div class="timeline-year">2023</div>
          <div class="timeline-content">
            <h3 class="timeline-title">品牌擴展</h3>
            <p class="timeline-desc">與超過 50 位國際設計師合作，並在台中、高雄開設分店，將美好生活帶給更多人。</p>
          </div>
        </div>
        <div class="timeline-item">
          <div class="timeline-year">2025</div>
          <div class="timeline-content">
            <h3 class="timeline-title">永續承諾</h3>
            <p class="timeline-desc">推出「MONO Green」計畫，承諾 2030 年前實現全產品線碳中和，為地球盡一份心力。</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Values Section -->
  <section class="values-section">
    <div class="values-inner">
      <div class="section-header">
        <p class="section-eyebrow">Our Values</p>
        <h2 class="section-title">我們堅持的價值</h2>
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
      </div>
    </div>
  </section>

  <!-- Craftsman Section -->
  <section class="craftsman-section">
    <div class="craftsman-inner">
      <div class="craftsman-image">
        <img src="https://images.unsplash.com/photo-1452860606245-08befc0ff44b?w=800&q=80" alt="職人精神">
      </div>
      <div class="craftsman-content">
        <p class="craftsman-eyebrow">Craftsmanship</p>
        <h2 class="craftsman-title">職人精神</h2>
        <p class="craftsman-desc">
          我們與世界各地的職人合作，從日本的陶藝師、丹麥的傢俱工匠，到台灣的編織達人。每一位職人都以數十年的經驗，將心血注入每件作品。
        </p>
        <p class="craftsman-desc">
          在 MONO，我們不只販售商品，更傳遞職人的故事與精神。每一件選物，都是人與物之間最真誠的對話。
        </p>
      </div>
    </div>
  </section>

  <!-- Quote Section -->
  <section class="quote-section">
    <div class="quote-inner">
      <blockquote class="quote-text">
        「生活中的每一件物品，都應該帶來美好與愉悅。」
      </blockquote>
      <p class="quote-author">— MONO 創辦人</p>
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
