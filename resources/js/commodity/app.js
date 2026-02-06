const { createApp, ref, computed, onMounted, onUnmounted, watch, nextTick } = Vue

createApp({
  setup() {
    const isScrolled = ref(false)
    const userMenuOpen = ref(false)
    const searchOpen = ref(false)
    const email = ref('')
    const searchQuery = ref('')
    const searchInput = ref(null)

    // 篩選狀態
    const selectedCategory = ref(null)
    const selectedBrand = ref(null)
    const priceRange = ref(null)
    const onlyOnSale = ref(false)
    const sortBy = ref('newest')

    // 從後端獲取數據
    const categories = ref(window.categoriesData || [])
    const brands = ref(window.brandsData || [])
    const products = ref(window.productsData || [])
    const filteredProducts = ref([...products.value])
    const wishlist = ref([])

    // 使用共用購物車模組
    const { cartItems, cartOpen, cartItemsCount, cartTotal, addToCart, updateQty, clearCart, goToCheckout } = window.CartModule.useCart()

    // 篩選商品
    const filterProducts = () => {
      let result = [...products.value]

      // 搜尋篩選
      if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase()
        result = result.filter(product =>
          product.name.toLowerCase().includes(query) ||
          product.brand.toLowerCase().includes(query) ||
          product.category.toLowerCase().includes(query)
        )
      }

      // 分類篩選
      if (selectedCategory.value !== null) {
        result = result.filter(product => product.category_id === selectedCategory.value)
      }

      // 品牌篩選
      if (selectedBrand.value !== null) {
        result = result.filter(product => product.brand_id === selectedBrand.value)
      }

      // 價格篩選
      if (priceRange.value) {
        switch (priceRange.value) {
          case 'under1000':
            result = result.filter(product => product.price < 1000)
            break
          case '1000to3000':
            result = result.filter(product => product.price >= 1000 && product.price <= 3000)
            break
          case '3000to5000':
            result = result.filter(product => product.price >= 3000 && product.price <= 5000)
            break
          case 'over5000':
            result = result.filter(product => product.price > 5000)
            break
        }
      }

      // 特價篩選
      if (onlyOnSale.value) {
        result = result.filter(product => product.originalPrice !== null)
      }

      filteredProducts.value = result
      sortProducts()
    }

    // 排序商品
    const sortProducts = () => {
      const sorted = [...filteredProducts.value]
      switch (sortBy.value) {
        case 'newest':
          sorted.sort((a, b) => b.id - a.id)
          break
        case 'price-low':
          sorted.sort((a, b) => a.price - b.price)
          break
        case 'price-high':
          sorted.sort((a, b) => b.price - a.price)
          break
        case 'name':
          sorted.sort((a, b) => a.name.localeCompare(b.name, 'zh-TW'))
          break
      }
      filteredProducts.value = sorted
    }

    // 重設篩選
    const resetFilters = () => {
      selectedCategory.value = null
      selectedBrand.value = null
      priceRange.value = null
      onlyOnSale.value = false
      searchQuery.value = ''
      sortBy.value = 'newest'
      filterProducts()
    }

    // 願望清單
    const toggleWishlist = (product) => {
      const index = wishlist.value.indexOf(product.id)
      if (index > -1) {
        wishlist.value.splice(index, 1)
      } else {
        wishlist.value.push(product.id)
      }
    }

    const isInWishlist = (productId) => {
      return wishlist.value.includes(productId)
    }

    const handleScroll = () => {
      isScrolled.value = window.scrollY > 50
    }

    const handleClickOutside = (event) => {
      if (userMenuOpen.value && !event.target.closest('.user-dropdown') && !event.target.closest('.icon-btn')) {
        userMenuOpen.value = false
      }
    }

    // 監聽搜尋開啟，自動聚焦輸入框
    watch(searchOpen, (newVal) => {
      if (newVal) {
        nextTick(() => {
          if (searchInput.value) {
            searchInput.value.focus()
          }
        })
      }
    })

    onMounted(() => {
      window.addEventListener('scroll', handleScroll)
      document.addEventListener('click', handleClickOutside)
      filterProducts()
    })

    onUnmounted(() => {
      window.removeEventListener('scroll', handleScroll)
      document.removeEventListener('click', handleClickOutside)
    })

    return {
      isScrolled,
      cartOpen,
      userMenuOpen,
      searchOpen,
      email,
      searchQuery,
      searchInput,
      selectedCategory,
      selectedBrand,
      priceRange,
      onlyOnSale,
      sortBy,
      categories,
      brands,
      products,
      filteredProducts,
      wishlist,
      cartItems,
      cartItemsCount,
      cartTotal,
      filterProducts,
      sortProducts,
      resetFilters,
      toggleWishlist,
      isInWishlist,
      addToCart,
      updateQty,
      clearCart,
      goToCheckout
    }
  }
}).mount('#app')
