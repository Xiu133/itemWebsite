<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>編輯商品 — MONO</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@300;400;500;600&family=Noto+Sans+TC:wght@300;400;500&display=swap" rel="stylesheet">
    @vite(['resources/css/ecommerce/style.css', 'resources/css/merchant/dashboard.css', 'resources/css/product/edit.css', 'resources/js/product/edit.js'])
</head>
<body>
    <!-- Header -->
    <header>
        <div class="header-inner">
            <a href="/" class="logo">MONO</a>
            <nav class="merchant-nav">
                <a href="{{ route('merchant.dashboard') }}">賣家中心</a>
            </nav>
            <div class="header-actions">
                <div class="user-menu">
                    <button class="icon-btn user-btn" id="user-btn">
                        <svg viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    </button>
                    <div class="user-dropdown" id="user-dropdown">
                        <div class="user-info">
                            <div class="user-name">{{ auth()->user()->name }}</div>
                            <div class="user-email">{{ auth()->user()->email }}</div>
                        </div>
                        <a href="/" class="user-menu-item">
                            <svg viewBox="0 0 24 24"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                            返回商店
                        </a>
                        <div class="user-menu-divider"></div>
                        <form action="{{ route('merchant.logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="user-menu-item logout-btn">
                                <svg viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                                登出
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="dashboard-page">
        <div class="dashboard-container" style="max-width: 800px;">
            <!-- Breadcrumb -->
            <div style="margin-bottom: 1.5rem;">
                <a href="{{ route('my-products.index') }}" class="back-link">
                    <svg viewBox="0 0 24 24"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                    返回商品管理
                </a>
            </div>

            <!-- Form Section -->
            <form class="form-section" method="POST" action="{{ route('my-products.update', $product->id) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="section-header">
                    <h2 class="section-title">編輯商品</h2>
                </div>

                <div class="form-body">
                    <div class="form-group">
                        <label class="form-label">
                            商品名稱 <span class="required">*</span>
                        </label>
                        <input type="text" name="name" class="form-input" value="{{ old('name', $product->name) }}" required>
                        @error('name')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">
                                分類 <span class="required">*</span>
                            </label>
                            <select name="category_id" class="form-select" required>
                                <option value="">選擇分類</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('category_id')
                                <p class="form-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label">
                                品牌 <span class="required">*</span>
                            </label>
                            <select name="brand_id" class="form-select" required>
                                <option value="">選擇品牌</option>
                                @foreach($brands as $brand)
                                    <option value="{{ $brand->id }}" {{ old('brand_id', $product->brand_id) == $brand->id ? 'selected' : '' }}>
                                        {{ $brand->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('brand_id')
                                <p class="form-error">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">商品描述</label>
                        <textarea name="description" class="form-input form-textarea">{{ old('description', $product->description) }}</textarea>
                        @error('description')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">
                                售價 <span class="required">*</span>
                            </label>
                            <input type="number" name="price" class="form-input" value="{{ old('price', $product->price) }}" min="0" step="1" required>
                            @error('price')
                                <p class="form-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label">原價</label>
                            <input type="number" name="original_price" class="form-input" value="{{ old('original_price', $product->original_price) }}" min="0" step="1">
                            <p class="form-hint">原價高於售價時會顯示折扣</p>
                            @error('original_price')
                                <p class="form-error">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            庫存數量 <span class="required">*</span>
                        </label>
                        <input type="number" name="stock" class="form-input" value="{{ old('stock', $product->stock) }}" min="0" required>
                        @error('stock')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">商品圖片</label>
                        @if($product->image)
                            <div class="current-image">
                                <img src="/images/{{ $product->image }}" alt="{{ $product->name }}">
                                <p>目前圖片</p>
                            </div>
                        @endif
                        <label class="image-upload">
                            <svg viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                            <p>點擊更換圖片</p>
                            <input type="file" name="image" id="image-input" accept="image/*">
                        </label>
                        <div class="image-preview" id="image-preview" style="display: none;">
                            <img id="preview-img" src="" alt="預覽">
                        </div>
                        @error('image')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="form-group">
                        <div class="form-checkbox">
                            <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $product->is_active) ? 'checked' : '' }}>
                            <label for="is_active">上架中</label>
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <a href="{{ route('my-products.index') }}" class="btn-cancel">取消</a>
                    <button type="submit" class="btn-submit">儲存變更</button>
                </div>
            </form>
        </div>
    </main>

    <!-- Footer -->
    <footer class="dashboard-footer">
        <div class="footer-inner">
            <p class="footer-copyright">&copy; 2026 MONO. All rights reserved.</p>
        </div>
    </footer>

   
</body>
</html>
