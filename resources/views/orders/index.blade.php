<!DOCTYPE html>
<html lang="zh-TW">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>我的訂單 — MONO</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@300;400;500;600&family=Noto+Sans+TC:wght@300;400;500&display=swap" rel="stylesheet">

  @vite(['resources/css/orders/style.css'])
</head>
<body>
  <!-- Header -->
  <header>
    <div class="header-inner">
      <a href="/" class="logo">MONO</a>
      <nav>
        <a href="{{ route('discount.index') }}">限時優惠</a>
        <a href="{{ route('commodity.index') }}">精選商品</a>
        <a href="{{ route('aboutus.index') }}">關於我們</a>
        <a href="{{ route('brandstory.index') }}">品牌故事</a>
      </nav>
      <div class="header-actions">
        <a href="/" class="back-to-shop">
          <svg viewBox="0 0 24 24"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
          返回購物
        </a>
      </div>
    </div>
  </header>

  <!-- Main Content -->
  <main class="orders-page">
    <div class="orders-container">
      <h1 class="page-title">我的訂單</h1>

      @if($orders->isEmpty())
      <div class="empty-orders">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
          <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
          <polyline points="14 2 14 8 20 8"/>
          <line x1="16" y1="13" x2="8" y2="13"/>
          <line x1="16" y1="17" x2="8" y2="17"/>
        </svg>
        <p>目前沒有任何訂單</p>
        <a href="{{ route('commodity.index') }}" class="btn-primary">開始購物</a>
      </div>
      @else
      <div class="orders-list">
        @foreach($orders as $order)
        <div class="order-card">
          <div class="order-header">
            <div class="order-info">
              <span class="order-number">訂單編號：{{ $order->order_number }}</span>
              <span class="order-date">{{ $order->created_at->format('Y/m/d H:i') }}</span>
            </div>
            <span class="order-status status-{{ $order->status }}">{{ $order->status_text }}</span>
          </div>

          <div class="order-body">
            <div class="order-summary">
              <div class="summary-row">
                <span>付款方式</span>
                <span>{{ $order->payment_method_text }}</span>
              </div>
              <div class="summary-row">
                <span>商品小計</span>
                <span>NT$ {{ number_format($order->subtotal) }}</span>
              </div>
              <div class="summary-row">
                <span>運費</span>
                <span>{{ $order->shipping_fee == 0 ? '免運費' : 'NT$ ' . number_format($order->shipping_fee) }}</span>
              </div>
              <div class="summary-row total">
                <span>訂單總額</span>
                <span>NT$ {{ number_format($order->total) }}</span>
              </div>
            </div>
          </div>

          <div class="order-footer">
            <a href="{{ route('orders.show', $order->order_number) }}" class="btn-outline">查看詳情</a>
          </div>
        </div>
        @endforeach
      </div>
      @endif
    </div>
  </main>

  <!-- Footer -->
  <footer>
    <div class="footer-inner">
      <p>&copy; 2026 MONO. All rights reserved.</p>
    </div>
  </footer>
</body>
</html>
