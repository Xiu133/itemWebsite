<!DOCTYPE html>
<html lang="zh-TW">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>訂單詳情 — MONO</title>
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
        <a href="{{ route('orders.index') }}" class="back-to-shop">
          <svg viewBox="0 0 24 24"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
          返回訂單列表
        </a>
      </div>
    </div>
  </header>

  <!-- Main Content -->
  <main class="orders-page">
    <div class="orders-container">
      <h1 class="page-title">訂單詳情</h1>

      <div class="order-detail-card">
        <div class="order-header">
          <div class="order-info">
            <span class="order-number">訂單編號：{{ $order->order_number }}</span>
            <span class="order-date">{{ $order->created_at->format('Y/m/d H:i') }}</span>
          </div>
          <span class="order-status status-{{ $order->status }}">{{ $order->status_text }}</span>
        </div>

        <div class="order-section">
          <h3 class="section-title">訂購商品</h3>
          <div class="order-items">
            @foreach($order->items as $item)
            <div class="order-item">
              <div class="item-image">
                <img src="{{ $item->product_image ? (str_starts_with($item->product_image, '/') ? $item->product_image : '/images/' . $item->product_image) : '/images/placeholder.png' }}" alt="{{ $item->product_name }}">
              </div>
              <div class="item-info">
                <h4 class="item-name">{{ $item->product_name }}</h4>
                <p class="item-price">NT$ {{ number_format($item->price) }} x {{ $item->quantity }}</p>
              </div>
              <div class="item-subtotal">
                NT$ {{ number_format($item->subtotal) }}
              </div>
            </div>
            @endforeach
          </div>
        </div>

        <div class="order-section">
          <h3 class="section-title">收件資訊</h3>
          <div class="shipping-info">
            <div class="info-row">
              <span class="label">收件人</span>
              <span class="value">{{ $order->shipping_name }}</span>
            </div>
            <div class="info-row">
              <span class="label">聯絡電話</span>
              <span class="value">{{ $order->shipping_phone }}</span>
            </div>
            <div class="info-row">
              <span class="label">收件地址</span>
              <span class="value">{{ $order->shipping_city }}{{ $order->shipping_district }}{{ $order->shipping_address }}</span>
            </div>
            @if($order->note)
            <div class="info-row">
              <span class="label">訂單備註</span>
              <span class="value">{{ $order->note }}</span>
            </div>
            @endif
          </div>
        </div>

        <div class="order-section">
          <h3 class="section-title">付款資訊</h3>
          <div class="payment-info">
            <div class="info-row">
              <span class="label">付款方式</span>
              <span class="value">{{ $order->payment_method_text }}</span>
            </div>
          </div>
        </div>

        <div class="order-total-section">
          <div class="total-row">
            <span>商品小計</span>
            <span>NT$ {{ number_format($order->subtotal) }}</span>
          </div>
          <div class="total-row">
            <span>運費</span>
            <span>{{ $order->shipping_fee == 0 ? '免運費' : 'NT$ ' . number_format($order->shipping_fee) }}</span>
          </div>
          @if($order->discount > 0)
          <div class="total-row discount">
            <span>折扣</span>
            <span>-NT$ {{ number_format($order->discount) }}</span>
          </div>
          @endif
          <div class="total-row grand-total">
            <span>訂單總額</span>
            <span>NT$ {{ number_format($order->total) }}</span>
          </div>
        </div>
      </div>
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
