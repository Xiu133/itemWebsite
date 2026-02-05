/**
 * 搜尋功能模組
 * 類似購物車的運作方式，點擊搜尋按鈕後彈出搜尋視窗
 */

// Header 滾動效果
(function() {
  window.addEventListener('scroll', function() {
    const header = document.getElementById('header');
    if (header) {
      if (window.scrollY > 50) {
        header.classList.add('scrolled');
      } else {
        header.classList.remove('scrolled');
      }
    }
  });
})();

(function() {
  'use strict';

  // DOM 元素
  const searchBtn = document.getElementById('search-btn');
  const searchOverlay = document.getElementById('search-overlay');
  const searchInput = document.getElementById('search-input');
  const searchClose = document.getElementById('search-close');
  const searchClear = document.getElementById('search-clear');
  const searchResults = document.getElementById('search-results');
  const searchLoading = document.getElementById('search-loading');
  const searchEmpty = document.getElementById('search-empty');
  const searchHint = document.getElementById('search-hint');

  // 狀態
  let searchTimeout = null;
  let currentQuery = '';

  // 開啟搜尋視窗
  function openSearch() {
    searchOverlay.classList.add('active');
    document.body.style.overflow = 'hidden';
    setTimeout(() => {
      searchInput.focus();
    }, 100);
  }

  // 關閉搜尋視窗
  function closeSearch() {
    searchOverlay.classList.remove('active');
    document.body.style.overflow = '';
    clearSearch();
  }

  // 清除搜尋
  function clearSearch() {
    searchInput.value = '';
    currentQuery = '';
    searchClear.style.display = 'none';
    showHint();
  }

  // 顯示提示
  function showHint() {
    searchResults.innerHTML = '';
    searchLoading.style.display = 'none';
    searchEmpty.style.display = 'none';
    searchHint.style.display = 'flex';
  }

  // 顯示載入中
  function showLoading() {
    searchResults.innerHTML = '';
    searchLoading.style.display = 'flex';
    searchEmpty.style.display = 'none';
    searchHint.style.display = 'none';
  }

  // 顯示無結果
  function showEmpty() {
    searchResults.innerHTML = '';
    searchLoading.style.display = 'none';
    searchEmpty.style.display = 'flex';
    searchHint.style.display = 'none';
  }

  // 顯示結果
  function showResults(products) {
    searchLoading.style.display = 'none';
    searchEmpty.style.display = 'none';
    searchHint.style.display = 'none';

    if (products.length === 0) {
      showEmpty();
      return;
    }

    const html = products.map(product => createResultItem(product)).join('');
    searchResults.innerHTML = html;

    // 如果有更多結果，顯示「查看全部」連結
    if (products.length >= 10) {
      const viewAllLink = document.createElement('a');
      viewAllLink.href = `/commodity?search=${encodeURIComponent(currentQuery)}`;
      viewAllLink.className = 'search-view-all';
      viewAllLink.textContent = '查看全部搜尋結果';
      searchResults.appendChild(viewAllLink);
    }
  }

  // 建立結果項目
  function createResultItem(product) {
    const priceHtml = product.original_price
      ? `<div class="current-price">NT$ ${formatPrice(product.price)}</div>
         <div class="original-price">NT$ ${formatPrice(product.original_price)}</div>`
      : `<div class="current-price">NT$ ${formatPrice(product.price)}</div>`;

    const tagHtml = product.tag
      ? `<span class="search-result-tag ${getTagClass(product.tag)}">${product.tag}</span>`
      : '';

    const imageHtml = product.image
      ? `<img src="${escapeHtml(product.image)}" alt="${escapeHtml(product.name)}">`
      : `<div class="no-image">
           <svg viewBox="0 0 24 24"><rect width="18" height="18" x="3" y="3" rx="2" ry="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/></svg>
         </div>`;

    return `
      <a href="/commodity?product=${product.id}" class="search-result-item">
        <div class="search-result-image">
          ${imageHtml}
        </div>
        <div class="search-result-info">
          <div class="search-result-name">${escapeHtml(product.name)}${tagHtml}</div>
          <div class="search-result-meta">
            ${product.brand ? `<span class="search-result-brand">${escapeHtml(product.brand)}</span>` : ''}
            ${product.category ? `<span class="search-result-category">${escapeHtml(product.category)}</span>` : ''}
          </div>
        </div>
        <div class="search-result-price">
          ${priceHtml}
        </div>
      </a>
    `;
  }

  // 格式化價格
  function formatPrice(price) {
    return new Intl.NumberFormat('zh-TW').format(price);
  }

  // 取得標籤 class
  function getTagClass(tag) {
    const tagLower = tag.toLowerCase();
    if (tagLower.includes('特價') || tagLower.includes('sale')) return 'sale';
    if (tagLower.includes('新') || tagLower.includes('new')) return 'new';
    if (tagLower.includes('熱') || tagLower.includes('hot')) return 'hot';
    return '';
  }

  // HTML 跳脫
  function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
  }

  // 執行搜尋
  async function performSearch(query) {
    if (!query.trim()) {
      showHint();
      return;
    }

    currentQuery = query;
    showLoading();

    try {
      const response = await fetch(`/api/search?q=${encodeURIComponent(query)}&limit=10`, {
        method: 'GET',
        headers: {
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        }
      });

      if (!response.ok) {
        throw new Error('搜尋請求失敗');
      }

      const result = await response.json();

      if (result.success) {
        showResults(result.data);
      } else {
        showEmpty();
      }
    } catch (error) {
      console.error('搜尋錯誤:', error);
      showEmpty();
    }
  }

  // 輸入處理（防抖）
  function handleInput(e) {
    const query = e.target.value;

    // 顯示/隱藏清除按鈕
    searchClear.style.display = query ? 'flex' : 'none';

    // 清除之前的 timeout
    if (searchTimeout) {
      clearTimeout(searchTimeout);
    }

    // 如果輸入為空，顯示提示
    if (!query.trim()) {
      showHint();
      return;
    }

    // 防抖：300ms 後執行搜尋
    searchTimeout = setTimeout(() => {
      performSearch(query);
    }, 300);
  }

  // 事件綑綁
  if (searchBtn) {
    searchBtn.addEventListener('click', openSearch);
  }

  if (searchClose) {
    searchClose.addEventListener('click', closeSearch);
  }

  if (searchClear) {
    searchClear.addEventListener('click', () => {
      clearSearch();
      searchInput.focus();
    });
  }

  if (searchOverlay) {
    searchOverlay.addEventListener('click', (e) => {
      if (e.target === searchOverlay) {
        closeSearch();
      }
    });
  }

  if (searchInput) {
    searchInput.addEventListener('input', handleInput);
    searchInput.addEventListener('keydown', (e) => {
      if (e.key === 'Escape') {
        closeSearch();
      }
    });
  }

  // ESC 鍵關閉
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && searchOverlay.classList.contains('active')) {
      closeSearch();
    }
  });
})();
