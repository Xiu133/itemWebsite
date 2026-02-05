<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * 建立訂單創建 Stored Procedure
     * 整合整個訂單創建流程，確保原子性
     */
    public function up(): void
    {
        // 建立訂單創建 Stored Procedure
        DB::unprepared("
            CREATE OR REPLACE FUNCTION sp_create_order(
                p_user_id BIGINT,
                p_cart_id BIGINT,
                p_order_number VARCHAR(50),
                p_shipping_name VARCHAR(255),
                p_shipping_phone VARCHAR(50),
                p_shipping_city VARCHAR(100),
                p_shipping_district VARCHAR(100),
                p_shipping_address TEXT,
                p_payment_method VARCHAR(50),
                p_note TEXT DEFAULT NULL
            )
            RETURNS TABLE (
                order_id BIGINT,
                order_number VARCHAR(50),
                subtotal DECIMAL(10,2),
                shipping_fee DECIMAL(10,2),
                discount DECIMAL(10,2),
                total DECIMAL(10,2),
                items_count INTEGER
            ) AS \$\$
            DECLARE
                v_order_id BIGINT;
                v_subtotal DECIMAL(10,2);
                v_shipping_fee DECIMAL(10,2);
                v_discount DECIMAL(10,2);
                v_total DECIMAL(10,2);
                v_items_count INTEGER;
                v_cart_item RECORD;
            BEGIN
                -- 檢查購物車是否存在且屬於該用戶
                IF NOT EXISTS (
                    SELECT 1 FROM carts
                    WHERE id = p_cart_id AND user_id = p_user_id
                ) THEN
                    RAISE EXCEPTION '購物車不存在或不屬於該用戶';
                END IF;

                -- 檢查購物車是否有商品
                SELECT COUNT(*) INTO v_items_count
                FROM cart_items
                WHERE cart_id = p_cart_id;

                IF v_items_count = 0 THEN
                    RAISE EXCEPTION '購物車是空的';
                END IF;

                -- 計算小計（從購物車項目）
                SELECT COALESCE(SUM(ci.quantity * p.price), 0) INTO v_subtotal
                FROM cart_items ci
                JOIN products p ON p.id = ci.product_id
                WHERE ci.cart_id = p_cart_id;

                -- 計算運費（滿 1000 免運費，否則 60 元）
                IF v_subtotal >= 1000 THEN
                    v_shipping_fee := 0;
                ELSE
                    v_shipping_fee := 60;
                END IF;

                -- 折扣（目前為 0，可以之後擴展）
                v_discount := 0;

                -- 計算總計
                v_total := v_subtotal + v_shipping_fee - v_discount;

                -- 建立訂單
                INSERT INTO orders (
                    user_id,
                    order_number,
                    status,
                    subtotal,
                    shipping_fee,
                    discount,
                    total,
                    shipping_name,
                    shipping_phone,
                    shipping_city,
                    shipping_district,
                    shipping_address,
                    payment_method,
                    note,
                    created_at,
                    updated_at
                ) VALUES (
                    p_user_id,
                    p_order_number,
                    'pending',
                    v_subtotal,
                    v_shipping_fee,
                    v_discount,
                    v_total,
                    p_shipping_name,
                    p_shipping_phone,
                    p_shipping_city,
                    p_shipping_district,
                    p_shipping_address,
                    p_payment_method,
                    p_note,
                    NOW(),
                    NOW()
                )
                RETURNING id INTO v_order_id;

                -- 建立訂單項目（會自動觸發庫存扣減 Trigger）
                INSERT INTO order_items (
                    order_id,
                    product_id,
                    product_name,
                    product_image,
                    quantity,
                    price,
                    subtotal,
                    created_at,
                    updated_at
                )
                SELECT
                    v_order_id,
                    ci.product_id,
                    p.name,
                    p.image,
                    ci.quantity,
                    p.price,
                    ci.quantity * p.price,
                    NOW(),
                    NOW()
                FROM cart_items ci
                JOIN products p ON p.id = ci.product_id
                WHERE ci.cart_id = p_cart_id;

                -- 清空購物車
                DELETE FROM cart_items WHERE cart_id = p_cart_id;

                -- 返回訂單資訊
                RETURN QUERY SELECT
                    v_order_id,
                    p_order_number,
                    v_subtotal,
                    v_shipping_fee,
                    v_discount,
                    v_total,
                    v_items_count;
            END;
            \$\$ LANGUAGE plpgsql;
        ");

        // 建立取消訂單 Stored Procedure（會恢復庫存）
        DB::unprepared("
            CREATE OR REPLACE FUNCTION sp_cancel_order(
                p_order_id BIGINT,
                p_user_id BIGINT
            )
            RETURNS BOOLEAN AS \$\$
            DECLARE
                v_order_status VARCHAR(50);
            BEGIN
                -- 檢查訂單是否存在且屬於該用戶
                SELECT status INTO v_order_status
                FROM orders
                WHERE id = p_order_id AND user_id = p_user_id
                FOR UPDATE;

                IF NOT FOUND THEN
                    RAISE EXCEPTION '訂單不存在或不屬於該用戶';
                END IF;

                -- 只有待付款狀態的訂單可以取消
                IF v_order_status != 'pending' THEN
                    RAISE EXCEPTION '只有待付款狀態的訂單可以取消，目前狀態為: %', v_order_status;
                END IF;

                -- 刪除訂單項目（會自動觸發庫存恢復 Trigger）
                DELETE FROM order_items WHERE order_id = p_order_id;

                -- 更新訂單狀態為已取消
                UPDATE orders
                SET status = 'cancelled',
                    updated_at = NOW()
                WHERE id = p_order_id;

                RETURN TRUE;
            END;
            \$\$ LANGUAGE plpgsql;
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared('DROP FUNCTION IF EXISTS sp_create_order(BIGINT, BIGINT, VARCHAR, VARCHAR, VARCHAR, VARCHAR, VARCHAR, TEXT, VARCHAR, TEXT);');
        DB::unprepared('DROP FUNCTION IF EXISTS sp_cancel_order(BIGINT, BIGINT);');
    }
};
