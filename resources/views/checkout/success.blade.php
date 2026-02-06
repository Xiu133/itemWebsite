<!DOCTYPE html>
<html lang="zh-TW">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>訂單完成 — MONO</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@300;400;500;600&family=Noto+Sans+TC:wght@300;400;500&display=swap" rel="stylesheet">

  @vite(['resources/css/checkout/style.css'])

  <!-- 購物車模組（用於結帳後清空） -->
  <script src="/js/cart.js"></script>
  <script>
    // 設定用戶 ID 並清空購物車
    window.CartModule.setUserId(@json(auth()->id()));
    window.CartModule.forceClearCart();
  </script>
</head>
<body>
  <div id="app">
    <!-- Header -->
    <header>
      <div class="header-inner">
        <a href="/" class="logo">MONO</a>
        <div class="checkout-steps">
          <div class="step completed">
            <span class="step-number">
              <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
            </span>
            <span class="step-text">確認商品</span>
          </div>
          <div class="step-line active"></div>
          <div class="step completed">
            <span class="step-number">
              <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
            </span>
            <span class="step-text">填寫資料</span>
          </div>
          <div class="step-line active"></div>
          <div class="step active">
            <span class="step-number">3</span>
            <span class="step-text">完成訂單</span>
          </div>
        </div>
        <a href="/" class="back-link">
          <svg viewBox="0 0 24 24"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
          返回購物
        </a>
      </div>
    </header>

    <main class="checkout-main">
      <div class="success-container">
        <div class="success-icon">
          <svg viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
        </div>

        <h1 class="success-title">訂單已成功建立！</h1>
        <p class="success-message">感謝您的購買，我們將盡快為您處理訂單。</p>

        <div class="order-info-card">
          <div class="order-info-header">
            <h2>訂單資訊</h2>
            <span class="order-status status-{{ $order->status }}">{{ $order->status_text }}</span>
          </div>

          <div class="order-info-row">
            <span class="label">訂單編號</span>
            <span class="value">{{ $order->order_number }}</span>
          </div>
          <div class="order-info-row">
            <span class="label">訂單日期</span>
            <span class="value">{{ $order->created_at->format('Y/m/d H:i') }}</span>
          </div>
          <div class="order-info-row">
            <span class="label">付款方式</span>
            <span class="value">{{ $order->payment_method_text }}</span>
          </div>

          <div class="order-divider"></div>

          <h3 class="order-section-title">收件資訊</h3>
          <div class="order-info-row">
            <span class="label">收件人</span>
            <span class="value">{{ $order->shipping_name }}</span>
          </div>
          <div class="order-info-row">
            <span class="label">聯絡電話</span>
            <span class="value">{{ $order->shipping_phone }}</span>
          </div>
          <div class="order-info-row">
            <span class="label">收件地址</span>
            <span class="value">{{ $order->shipping_city }}{{ $order->shipping_district }}{{ $order->shipping_address }}</span>
          </div>

          <div class="order-divider"></div>

          <h3 class="order-section-title">訂購商品</h3>
          <div class="order-items">
            @foreach($order->items as $item)
            <div class="order-item">
              <div class="order-item-image">
                <img src="{{ $item->product_image }}" alt="{{ $item->product_name }}">
              </div>
              <div class="order-item-info">
                <p class="order-item-name">{{ $item->product_name }}</p>
                <p class="order-item-price">NT$ {{ number_format($item->price) }} x {{ $item->quantity }}</p>
              </div>
              <div class="order-item-subtotal">
                NT$ {{ number_format($item->subtotal) }}
              </div>
            </div>
            @endforeach
          </div>

          <div class="order-divider"></div>

          <div class="order-summary-rows">
            <div class="order-info-row">
              <span class="label">小計</span>
              <span class="value">NT$ {{ number_format($order->subtotal) }}</span>
            </div>
            <div class="order-info-row">
              <span class="label">運費</span>
              <span class="value">{{ $order->shipping_fee == 0 ? '免運費' : 'NT$ ' . number_format($order->shipping_fee) }}</span>
            </div>
            @if($order->discount > 0)
            <div class="order-info-row">
              <span class="label">折扣</span>
              <span class="value discount">-NT$ {{ number_format($order->discount) }}</span>
            </div>
            @endif
            <div class="order-info-row total">
              <span class="label">訂單總額</span>
              <span class="value">NT$ {{ number_format($order->total) }}</span>
            </div>
          </div>
        </div>

        @if($order->payment_method === 'bank_transfer')
        <div class="payment-info-card">
          <h3>
            <svg viewBox="0 0 24 24"><path d="M3 21h18M3 10h18M5 6l7-3 7 3M4 10v11M20 10v11M8 14v3M12 14v3M16 14v3"/></svg>
            銀行轉帳資訊
          </h3>
          <div class="bank-info">
            <div class="bank-info-row">
              <span class="label">銀行名稱</span>
              <span class="value">國泰世華銀行 (013)</span>
            </div>
            <div class="bank-info-row">
              <span class="label">帳號</span>
              <span class="value">1234-5678-9012-3456</span>
            </div>
            <div class="bank-info-row">
              <span class="label">戶名</span>
              <span class="value">MONO 精選生活選物</span>
            </div>
            <div class="bank-info-row">
              <span class="label">應付金額</span>
              <span class="value highlight">NT$ {{ number_format($order->total) }}</span>
            </div>
          </div>
          <p class="bank-notice">請於 3 日內完成轉帳，並保留轉帳收據以便查詢。</p>
        </div>
        @endif

        <div class="success-actions">
          <a href="/" class="btn btn-primary">繼續購物</a>
          <a href="{{ route('orders.index') }}" class="btn btn-secondary">查看訂單</a>
        </div>
      </div>
    </main>

    <!-- Footer -->
    <footer>
      <div class="footer-inner">
        <p>&copy; 2026 MONO. All rights reserved.</p>
      </div>
    </footer>
  </div>
</body>
</html>
