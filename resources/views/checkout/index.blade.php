<!DOCTYPE html>
<html lang="zh-TW">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>結帳 — MONO</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@300;400;500;600&family=Noto+Sans+TC:wght@300;400;500&display=swap" rel="stylesheet">

  @vite(['resources/css/checkout/style.css'])

  <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>

  <script>
    window.checkoutData = @json($checkoutData);
    window.csrfToken = '{{ csrf_token() }}';
    window.userName = '{{ Auth::user()->name ?? '' }}';
    window.userPhone = '{{ Auth::user()->phone ?? '' }}';
  </script>

  @vite(['resources/js/checkout/app.js'])
</head>
<body>
  <div id="app">
    <!-- Header -->
    <header>
      <div class="header-inner">
        <a href="/" class="logo">MONO</a>
        <div class="checkout-steps">
          <div class="step" :class="{ active: currentStep >= 1, completed: currentStep > 1 }">
            <span class="step-number">1</span>
            <span class="step-text">確認商品</span>
          </div>
          <div class="step-line" :class="{ active: currentStep > 1 }"></div>
          <div class="step" :class="{ active: currentStep >= 2, completed: currentStep > 2 }">
            <span class="step-number">2</span>
            <span class="step-text">填寫資料</span>
          </div>
          <div class="step-line" :class="{ active: currentStep > 2 }"></div>
          <div class="step" :class="{ active: currentStep >= 3 }">
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
      <div class="checkout-container">
        <!-- 左側：商品清單與表單 -->
        <div class="checkout-left">
          <!-- Step 1: 確認商品 -->
          <section class="checkout-section" v-show="currentStep === 1">
            <h2 class="section-title">確認購物車商品</h2>

            <div class="cart-items-list">
              <div class="cart-item" v-for="item in cartItems" :key="item.id">
                <div class="item-image">
                  <img :src="item.image" :alt="item.name">
                </div>
                <div class="item-details">
                  <h3 class="item-name">@{{ item.name }}</h3>
                  <p class="item-price">NT$ @{{ item.price.toLocaleString() }}</p>
                  <p class="item-qty">數量：@{{ item.quantity }}</p>
                </div>
                <div class="item-subtotal">
                  NT$ @{{ item.subtotal.toLocaleString() }}
                </div>
              </div>
            </div>

            <div class="free-shipping-notice" v-if="summary.amount_to_free_shipping > 0">
              <svg viewBox="0 0 24 24"><path d="M1 3h15v13H1zM16 8h4l3 3v5h-7V8z"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
              <span>再購買 NT$ @{{ summary.amount_to_free_shipping.toLocaleString() }} 即可享免運費！</span>
            </div>
            <div class="free-shipping-success" v-else>
              <svg viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
              <span>恭喜！您已達到免運門檻</span>
            </div>

            <button class="btn btn-primary btn-block" @click="nextStep">
              下一步：填寫收件資料
              <svg viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
            </button>
          </section>

          <!-- Step 2: 填寫資料 -->
          <section class="checkout-section" v-show="currentStep === 2">
            <h2 class="section-title">收件人資料</h2>

            <form @submit.prevent="submitOrder" class="checkout-form">
              <div class="form-row">
                <div class="form-group">
                  <label for="shipping_name">收件人姓名 <span class="required">*</span></label>
                  <input type="text" id="shipping_name" v-model="form.shipping_name" required placeholder="請輸入收件人姓名">
                  <span class="error-message" v-if="errors.shipping_name">@{{ errors.shipping_name }}</span>
                </div>
                <div class="form-group">
                  <label for="shipping_phone">聯絡電話 <span class="required">*</span></label>
                  <input type="tel" id="shipping_phone" v-model="form.shipping_phone" required placeholder="請輸入聯絡電話">
                  <span class="error-message" v-if="errors.shipping_phone">@{{ errors.shipping_phone }}</span>
                </div>
              </div>

              <div class="form-row">
                <div class="form-group">
                  <label for="shipping_city">城市 <span class="required">*</span></label>
                  <select id="shipping_city" v-model="form.shipping_city" @change="onCityChange" required>
                    <option value="">請選擇城市</option>
                    <option v-for="city in cities" :key="city" :value="city">@{{ city }}</option>
                  </select>
                  <span class="error-message" v-if="errors.shipping_city">@{{ errors.shipping_city }}</span>
                </div>
                <div class="form-group">
                  <label for="shipping_district">區域 <span class="required">*</span></label>
                  <select id="shipping_district" v-model="form.shipping_district" required :disabled="!form.shipping_city">
                    <option value="">請選擇區域</option>
                    <option v-for="district in currentDistricts" :key="district" :value="district">@{{ district }}</option>
                  </select>
                  <span class="error-message" v-if="errors.shipping_district">@{{ errors.shipping_district }}</span>
                </div>
              </div>

              <div class="form-group">
                <label for="shipping_address">詳細地址 <span class="required">*</span></label>
                <input type="text" id="shipping_address" v-model="form.shipping_address" required placeholder="請輸入詳細地址（路/街、巷、弄、號、樓）">
                <span class="error-message" v-if="errors.shipping_address">@{{ errors.shipping_address }}</span>
              </div>

              <h2 class="section-title" style="margin-top: 2rem;">付款方式</h2>

              <div class="payment-methods">
                <label class="payment-option" :class="{ selected: form.payment_method === 'credit_card' }">
                  <input type="radio" name="payment_method" value="credit_card" v-model="form.payment_method">
                  <div class="payment-icon">
                    <svg viewBox="0 0 24 24"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
                  </div>
                  <div class="payment-info">
                    <span class="payment-name">信用卡付款</span>
                    <span class="payment-desc">支援 ECPay 綠界金流</span>
                  </div>
                </label>

                <label class="payment-option" :class="{ selected: form.payment_method === 'cash_on_delivery' }">
                  <input type="radio" name="payment_method" value="cash_on_delivery" v-model="form.payment_method">
                  <div class="payment-icon">
                    <svg viewBox="0 0 24 24"><path d="M17 8h2a2 2 0 0 1 2 2v6a2 2 0 0 1-2 2h-2v4l-4-4H9a1.994 1.994 0 0 1-1.414-.586m0 0L11 14h4a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h2v4l.586-.586Z"/></svg>
                  </div>
                  <div class="payment-info">
                    <span class="payment-name">貨到付款</span>
                    <span class="payment-desc">商品送達時付款（+$30 手續費）</span>
                  </div>
                </label>
              </div>
              <span class="error-message" v-if="errors.payment_method">@{{ errors.payment_method }}</span>

              <div class="form-group">
                <label for="note">訂單備註（選填）</label>
                <textarea id="note" v-model="form.note" rows="3" placeholder="如有特殊需求請在此說明"></textarea>
              </div>

              <div class="form-actions">
                <button type="button" class="btn btn-secondary" @click="prevStep">
                  <svg viewBox="0 0 24 24"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                  返回上一步
                </button>
                <button type="submit" class="btn btn-primary" :disabled="isSubmitting">
                  <span v-if="isSubmitting">處理中...</span>
                  <span v-else>確認下單</span>
                </button>
              </div>
            </form>
          </section>
        </div>

        <!-- 右側：訂單摘要 -->
        <aside class="checkout-right">
          <div class="order-summary">
            <h3 class="summary-title">訂單摘要</h3>

            <div class="summary-items">
              <div class="summary-item" v-for="item in cartItems" :key="item.id">
                <div class="summary-item-image">
                  <img :src="item.image" :alt="item.name">
                  <span class="summary-item-qty">@{{ item.quantity }}</span>
                </div>
                <div class="summary-item-info">
                  <p class="summary-item-name">@{{ item.name }}</p>
                  <p class="summary-item-price">NT$ @{{ item.subtotal.toLocaleString() }}</p>
                </div>
              </div>
            </div>

            <div class="summary-divider"></div>

            <div class="summary-row">
              <span>小計</span>
              <span>NT$ @{{ summary.subtotal.toLocaleString() }}</span>
            </div>
            <div class="summary-row">
              <span>運費</span>
              <span :class="{ 'free-shipping': summary.shipping_fee === 0 }">
                @{{ summary.shipping_fee === 0 ? '免運費' : 'NT$ ' + summary.shipping_fee.toLocaleString() }}
              </span>
            </div>
            <div class="summary-row" v-if="summary.discount > 0">
              <span>折扣</span>
              <span class="discount">-NT$ @{{ summary.discount.toLocaleString() }}</span>
            </div>

            <div class="summary-divider"></div>

            <div class="summary-row total">
              <span>總計</span>
              <span>NT$ @{{ totalAmount.toLocaleString() }}</span>
            </div>

            <div class="summary-notice">
              <svg viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
              <span>安全結帳，資料加密保護</span>
            </div>
          </div>
        </aside>
      </div>
    </main>

    <!-- Footer -->
    <footer>
      <div class="footer-inner">
        <p>&copy; 2026 MONO. All rights reserved.</p>
      </div>
    </footer>

    <!-- Loading Overlay -->
    <div class="loading-overlay" v-if="isSubmitting">
      <div class="loading-spinner"></div>
      <p>正在處理您的訂單...</p>
    </div>
  </div>
</body>
</html>
