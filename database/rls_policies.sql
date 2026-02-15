-- ============================================================
-- Supabase Row Level Security (RLS) Policies
-- ============================================================
-- 目的：防止透過 Supabase REST API (anon key) 直接存取敏感資料
-- Laravel 使用 postgres superuser 連線，自動繞過 RLS，不受影響
-- ============================================================

-- ============================================================
-- 1. 公開資料 (Public Tables) — anon 可以 SELECT
-- ============================================================

-- === categories ===
ALTER TABLE categories ENABLE ROW LEVEL SECURITY;

CREATE POLICY "categories_public_read"
    ON categories FOR SELECT
    TO anon, authenticated
    USING (is_active = true);

-- === brands ===
ALTER TABLE brands ENABLE ROW LEVEL SECURITY;

CREATE POLICY "brands_public_read"
    ON brands FOR SELECT
    TO anon, authenticated
    USING (is_active = true);

-- === tags ===
ALTER TABLE tags ENABLE ROW LEVEL SECURITY;

CREATE POLICY "tags_public_read"
    ON tags FOR SELECT
    TO anon, authenticated
    USING (is_active = true);

-- === products ===
ALTER TABLE products ENABLE ROW LEVEL SECURITY;

CREATE POLICY "products_public_read"
    ON products FOR SELECT
    TO anon, authenticated
    USING (is_active = true AND deleted_at IS NULL);

-- === product_tag (多對多關聯) ===
ALTER TABLE product_tag ENABLE ROW LEVEL SECURITY;

CREATE POLICY "product_tag_public_read"
    ON product_tag FOR SELECT
    TO anon, authenticated
    USING (true);

-- ============================================================
-- 2. 敏感資料 (Private Tables) — 完全封鎖 anon/authenticated
--    只有 Laravel (postgres superuser) 可以存取
-- ============================================================

-- === users ===
ALTER TABLE users ENABLE ROW LEVEL SECURITY;
-- 不建立任何 policy → anon/authenticated 完全無法存取

-- === sellers ===
ALTER TABLE sellers ENABLE ROW LEVEL SECURITY;

-- 允許公開讀取商家基本資訊（shop_name, description）
CREATE POLICY "sellers_public_read"
    ON sellers FOR SELECT
    TO anon, authenticated
    USING (true);
-- 注意：密碼等敏感欄位在 users 表，sellers 表只有商家資訊

-- === orders ===
ALTER TABLE orders ENABLE ROW LEVEL SECURITY;
-- 不建立任何 policy → 完全封鎖

-- === order_items ===
ALTER TABLE order_items ENABLE ROW LEVEL SECURITY;
-- 不建立任何 policy → 完全封鎖

-- === payments ===
ALTER TABLE payments ENABLE ROW LEVEL SECURITY;
-- 不建立任何 policy → 完全封鎖

-- === carts ===
ALTER TABLE carts ENABLE ROW LEVEL SECURITY;
-- 不建立任何 policy → 完全封鎖

-- === cart_items ===
ALTER TABLE cart_items ENABLE ROW LEVEL SECURITY;
-- 不建立任何 policy → 完全封鎖

-- ============================================================
-- 3. 系統內部表 (System Tables) — 完全封鎖
-- ============================================================

-- === sessions ===
ALTER TABLE sessions ENABLE ROW LEVEL SECURITY;
-- 不建立任何 policy → 完全封鎖

-- === password_reset_tokens ===
ALTER TABLE password_reset_tokens ENABLE ROW LEVEL SECURITY;
-- 不建立任何 policy → 完全封鎖

-- === cache ===
ALTER TABLE cache ENABLE ROW LEVEL SECURITY;
-- 不建立任何 policy → 完全封鎖

-- === cache_locks ===
ALTER TABLE cache_locks ENABLE ROW LEVEL SECURITY;
-- 不建立任何 policy → 完全封鎖

-- === migrations ===
ALTER TABLE migrations ENABLE ROW LEVEL SECURITY;
-- 不建立任何 policy → 完全封鎖

-- === activity_log ===
ALTER TABLE activity_log ENABLE ROW LEVEL SECURITY;
-- 不建立任何 policy → 完全封鎖

-- ============================================================
-- 驗證：列出所有 RLS 狀態
-- ============================================================
-- 執行以下查詢確認 RLS 已啟用：
-- SELECT tablename, rowsecurity FROM pg_tables WHERE schemaname = 'public';
