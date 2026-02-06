/**
 * 共用購物車模組
 * 所有頁面都使用這個模組來管理購物車
 * 購物車資料以用戶 ID 區分存儲在 localStorage
 */

(function() {
  'use strict';

  const CART_PREFIX = 'cart_';
  let currentUserId = null;

  // 取得當前購物車的 localStorage key
  function getCartKey() {
    return currentUserId ? `${CART_PREFIX}user_${currentUserId}` : `${CART_PREFIX}guest`;
  }

  // 設定當前用戶 ID
  function setUserId(userId) {
    currentUserId = userId;
  }

  // 取得當前用戶 ID
  function getUserId() {
    return currentUserId;
  }

  // 從 localStorage 載入購物車
  function loadCart() {
    try {
      const saved = localStorage.getItem(getCartKey());
      if (saved) {
        return JSON.parse(saved);
      }
    } catch (e) {
      console.error('載入購物車失敗:', e);
    }
    return [];
  }

  // 儲存購物車到 localStorage
  function saveCart(items) {
    try {
      localStorage.setItem(getCartKey(), JSON.stringify(items));
    } catch (e) {
      console.error('儲存購物車失敗:', e);
    }
  }

  // 清除當前用戶的購物車（不需確認）
  function forceClearCart() {
    try {
      localStorage.removeItem(getCartKey());
    } catch (e) {
      console.error('清除購物車失敗:', e);
    }
  }

  // 清除所有訪客購物車
  function clearGuestCart() {
    try {
      localStorage.removeItem(`${CART_PREFIX}guest`);
    } catch (e) {
      console.error('清除訪客購物車失敗:', e);
    }
  }

  // Vue 3 Composition API 用的 composable
  function useCart() {
    const { ref, computed, onMounted, watch } = Vue;

    const cartItems = ref([]);
    const cartOpen = ref(false);

    const cartItemsCount = computed(() => {
      return cartItems.value.reduce((sum, item) => sum + item.qty, 0);
    });

    const cartTotal = computed(() => {
      return cartItems.value.reduce((sum, item) => sum + item.price * item.qty, 0);
    });

    const addToCart = (product) => {
      const existing = cartItems.value.find(item => item.id === product.id);
      if (existing) {
        existing.qty++;
      } else {
        cartItems.value.push({
          id: product.id,
          name: product.name,
          brand: product.brand || '',
          price: product.price,
          image: product.image || '',
          qty: 1
        });
      }
    };

    const updateQty = (item, delta) => {
      item.qty += delta;
      if (item.qty <= 0) {
        const index = cartItems.value.indexOf(item);
        cartItems.value.splice(index, 1);
      }
    };

    const clearCart = () => {
      if (confirm('確定要清空購物車嗎？')) {
        cartItems.value = [];
      }
    };

    // 強制清空購物車（不需確認，用於結帳完成後）
    const forceClear = () => {
      cartItems.value = [];
      forceClearCart();
    };

    const goToCheckout = async () => {
      if (cartItems.value.length === 0) {
        alert('購物車是空的');
        return;
      }

      try {
        const items = cartItems.value.map(item => ({
          product_id: item.id,
          quantity: item.qty
        }));

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

        const response = await fetch('/api/cart/sync', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken
          },
          body: JSON.stringify({ items })
        });

        const data = await response.json();

        if (data.success) {
          window.location.href = '/checkout';
        } else {
          alert(data.message || '同步購物車失敗，請稍後再試');
        }
      } catch (error) {
        console.error('Checkout error:', error);
        alert('發生錯誤，請稍後再試');
      }
    };

    // 重新載入購物車（用於用戶切換時）
    const reloadCart = () => {
      cartItems.value = loadCart();
    };

    // 初始化：從 localStorage 載入購物車
    onMounted(() => {
      cartItems.value = loadCart();
    });

    // 監聽變化並儲存
    watch(cartItems, (newVal) => {
      saveCart(newVal);
    }, { deep: true });

    return {
      cartItems,
      cartOpen,
      cartItemsCount,
      cartTotal,
      addToCart,
      updateQty,
      clearCart,
      forceClear,
      goToCheckout,
      reloadCart
    };
  }

  // Vue 3 Options API 用的 mixin
  const cartMixin = {
    data() {
      return {
        cartItems: [],
        cartOpen: false
      };
    },
    computed: {
      cartItemsCount() {
        return this.cartItems.reduce((sum, item) => sum + item.qty, 0);
      },
      cartTotal() {
        return this.cartItems.reduce((sum, item) => sum + item.price * item.qty, 0);
      }
    },
    methods: {
      addToCart(product) {
        const existing = this.cartItems.find(item => item.id === product.id);
        if (existing) {
          existing.qty++;
        } else {
          this.cartItems.push({
            id: product.id,
            name: product.name,
            brand: product.brand || '',
            price: product.price,
            image: product.image || '',
            qty: 1
          });
        }
        this.cartOpen = true;
      },
      updateQty(item, delta) {
        item.qty += delta;
        if (item.qty <= 0) {
          this.cartItems = this.cartItems.filter(i => i.id !== item.id);
        }
      },
      clearCart() {
        if (confirm('確定要清空購物車嗎？')) {
          this.cartItems = [];
        }
      },
      forceClear() {
        this.cartItems = [];
        forceClearCart();
      },
      reloadCart() {
        this.cartItems = loadCart();
      },
      goToCheckout() {
        if (this.cartItems.length === 0) {
          alert('購物車是空的');
          return;
        }

        const items = this.cartItems.map(item => ({
          product_id: item.id,
          quantity: item.qty
        }));

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

        fetch('/api/cart/sync', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken
          },
          body: JSON.stringify({ items })
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            window.location.href = '/checkout';
          } else {
            alert(data.message || '同步購物車失敗，請稍後再試');
          }
        })
        .catch(error => {
          console.error('Checkout error:', error);
          alert('發生錯誤，請稍後再試');
        });
      }
    },
    mounted() {
      this.cartItems = loadCart();

      this.$watch('cartItems', (newVal) => {
        saveCart(newVal);
      }, { deep: true });
    }
  };

  // 暴露到全局
  window.CartModule = {
    useCart,
    cartMixin,
    loadCart,
    saveCart,
    setUserId,
    getUserId,
    forceClearCart,
    clearGuestCart
  };
})();
