
const { createApp, ref, computed, onMounted, onUnmounted } = Vue

createApp({
  setup() {
    const isScrolled = ref(false)
    const userMenuOpen = ref(false)
    const email = ref('')
    const isCheckingOut = ref(false)

    // 从后端获取数据（Laravel 传递的数据）
    const categories = ref(window.categoriesData || [])
    const products = ref(window.productsData || [])

    // 使用共用購物車模組
    const { cartItems, cartOpen, cartItemsCount, cartTotal, addToCart, updateQty, clearCart, goToCheckout: cartGoToCheckout } = window.CartModule.useCart()

    const subscribe = () => {
      if (email.value) {
        alert('感謝您的訂閱！')
        email.value = ''
      }
    }

    const goToCheckout = async () => {
      isCheckingOut.value = true
      try {
        await cartGoToCheckout()
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
