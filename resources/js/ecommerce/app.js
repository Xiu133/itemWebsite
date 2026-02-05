
    const { createApp, ref, computed, onMounted, onUnmounted } = Vue

    createApp({
      setup() {
        const isScrolled = ref(false)
        const cartOpen = ref(false)
        const userMenuOpen = ref(false)
        const email = ref('')
        const isCheckingOut = ref(false)

        // 从后端获取数据（Laravel 传递的数据）
        const categories = ref(window.categoriesData || [])
        const products = ref(window.productsData || [])

        const cartItems = ref([])

        const cartItemsCount = computed(() => {
          return cartItems.value.reduce((sum, item) => sum + item.qty, 0)
        })

        const cartTotal = computed(() => {
          return cartItems.value.reduce((sum, item) => sum + item.price * item.qty, 0)
        })

        const addToCart = (product) => {
          const existing = cartItems.value.find(item => item.id === product.id)
          if (existing) {
            existing.qty++
          } else {
            cartItems.value.push({ ...product, qty: 1 })
          }
        }

        const updateQty = (item, delta) => {
          item.qty += delta
          if (item.qty <= 0) {
            const index = cartItems.value.indexOf(item)
            cartItems.value.splice(index, 1)
          }
        }

        const clearCart = () => {
          if (confirm('確定要清空購物車嗎？')) {
            cartItems.value = []
          }
        }

        const subscribe = () => {
          if (email.value) {
            alert('感謝您的訂閱！')
            email.value = ''
          }
        }

        const goToCheckout = async () => {
          if (cartItems.value.length === 0) {
            alert('購物車是空的')
            return
          }

          isCheckingOut.value = true

          try {
            // 準備同步資料
            const items = cartItems.value.map(item => ({
              product_id: item.id,
              quantity: item.qty
            }))

            // 取得 CSRF token
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content

            // 呼叫同步 API
            const response = await fetch('/api/cart/sync', {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
              },
              body: JSON.stringify({ items })
            })

            const data = await response.json()

            if (data.success) {
              // 同步成功，跳轉到結帳頁
              window.location.href = '/checkout'
            } else {
              alert(data.message || '同步購物車失敗，請稍後再試')
            }
          } catch (error) {
            console.error('Checkout error:', error)
            alert('發生錯誤，請稍後再試')
          } finally {
            isCheckingOut.value = false
          }
        }

        const handleScroll = () => {
          isScrolled.value = window.scrollY > 50
        }

        const handleClickOutside = (event) => {
          if (userMenuOpen.value && !event.target.closest('.user-dropdown') && !event.target.closest('.icon-btn')) {
            userMenuOpen.value = false
          }
        }

        onMounted(() => {
          window.addEventListener('scroll', handleScroll)
          document.addEventListener('click', handleClickOutside)
        })

        onUnmounted(() => {
          window.removeEventListener('scroll', handleScroll)
          document.removeEventListener('click', handleClickOutside)
        })

        return {
          isScrolled,
          cartOpen,
          userMenuOpen,
          email,
          isCheckingOut,
          categories,
          products,
          cartItems,
          cartItemsCount,
          cartTotal,
          addToCart,
          updateQty,
          clearCart,
          subscribe,
          goToCheckout
        }
      }
    }).mount('#app')
