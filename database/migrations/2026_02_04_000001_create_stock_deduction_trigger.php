<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * 建立庫存扣減 Trigger
     * 當 order_items 新增時自動扣減 products.stock
     * 防止超賣和 Race Condition
     */
    public function up(): void
    {
        // 建立庫存扣減函數
        DB::unprepared("
            CREATE OR REPLACE FUNCTION deduct_stock_on_order_item()
            RETURNS TRIGGER AS \$\$
            DECLARE
                v_current_stock INTEGER;
                v_product_name VARCHAR(255);
            BEGIN
                -- 取得當前庫存和商品名稱（加上行鎖 FOR UPDATE）
                SELECT stock, name INTO v_current_stock, v_product_name
                FROM products
                WHERE id = NEW.product_id
                FOR UPDATE;

                -- 檢查庫存是否足夠
                IF v_current_stock < NEW.quantity THEN
                    RAISE EXCEPTION '庫存不足: 商品「%」目前庫存 % 件，需要 % 件',
                        v_product_name, v_current_stock, NEW.quantity;
                END IF;

                -- 扣減庫存
                UPDATE products
                SET stock = stock - NEW.quantity,
                    updated_at = NOW()
                WHERE id = NEW.product_id;

                RETURN NEW;
            END;
            \$\$ LANGUAGE plpgsql;
        ");

        // 建立 Trigger
        DB::unprepared("
            CREATE TRIGGER trg_deduct_stock_on_order_item
            AFTER INSERT ON order_items
            FOR EACH ROW
            EXECUTE FUNCTION deduct_stock_on_order_item();
        ");

        // 建立庫存恢復函數（用於訂單取消時）
        DB::unprepared("
            CREATE OR REPLACE FUNCTION restore_stock_on_order_item_delete()
            RETURNS TRIGGER AS \$\$
            BEGIN
                -- 恢復庫存
                UPDATE products
                SET stock = stock + OLD.quantity,
                    updated_at = NOW()
                WHERE id = OLD.product_id;

                RETURN OLD;
            END;
            \$\$ LANGUAGE plpgsql;
        ");

        // 建立刪除時恢復庫存的 Trigger
        DB::unprepared("
            CREATE TRIGGER trg_restore_stock_on_order_item_delete
            BEFORE DELETE ON order_items
            FOR EACH ROW
            EXECUTE FUNCTION restore_stock_on_order_item_delete();
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS trg_deduct_stock_on_order_item ON order_items;');
        DB::unprepared('DROP TRIGGER IF EXISTS trg_restore_stock_on_order_item_delete ON order_items;');
        DB::unprepared('DROP FUNCTION IF EXISTS deduct_stock_on_order_item();');
        DB::unprepared('DROP FUNCTION IF EXISTS restore_stock_on_order_item_delete();');
    }
};
