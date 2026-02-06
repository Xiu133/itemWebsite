/**
 * 限時優惠頁面 Vue App
 */
const { createApp } = Vue;

createApp({
  mixins: [window.CartModule.cartMixin],
  data() {
    return {
      isScrolled: false,
      userMenuOpen: false,
      email: '',
      products: window.productsData || [],
      categories: window.categoriesData || [],
      selectedCategory: null,
      loading: false,
      countdown: {
        days: 0,
        hours: 0,
        minutes: 0,
        seconds: 0
      }
    };
  },
  computed: {
    filteredProducts() {
      if (this.selectedCategory === null) {
        return this.products;
      }
      return this.products.filter(p => p.categoryId === this.selectedCategory);
    }
  },
  methods: {
    filterByCategory(categoryId) {
      this.selectedCategory = categoryId;
    },
    subscribe() {
      if (this.email) {
        alert('感謝您的訂閱！');
        this.email = '';
      }
    },
    startCountdown() {
      // 設定活動結束時間（示例：7天後）
      const endDate = new Date();
      endDate.setDate(endDate.getDate() + 7);
      endDate.setHours(23, 59, 59, 999);

      const updateCountdown = () => {
        const now = new Date();
        const diff = endDate - now;

        if (diff <= 0) {
          this.countdown = { days: 0, hours: 0, minutes: 0, seconds: 0 };
          return;
        }

        this.countdown.days = Math.floor(diff / (1000 * 60 * 60 * 24));
        this.countdown.hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        this.countdown.minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
        this.countdown.seconds = Math.floor((diff % (1000 * 60)) / 1000);
      };

      updateCountdown();
      setInterval(updateCountdown, 1000);
    }
  },
  mounted() {
    // Scroll handler
    window.addEventListener('scroll', () => {
      this.isScrolled = window.scrollY > 50;
    });

    // Close user menu when clicking outside
    document.addEventListener('click', (e) => {
      if (!e.target.closest('.user-dropdown') && !e.target.closest('.icon-btn')) {
        this.userMenuOpen = false;
      }
    });

    // Start countdown
    this.startCountdown();
  }
}).mount('#app');
