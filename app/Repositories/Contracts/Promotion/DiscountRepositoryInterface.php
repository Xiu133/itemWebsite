<?php

namespace App\Repositories\Contracts\Promotion;

use Illuminate\Support\Collection;

interface DiscountRepositoryInterface
{
    /**
     * 取得所有特價商品
     */
    public function getOnSaleProducts(): Collection;

    /**
     * 取得限時優惠商品（有時間限制的特價）
     */
    public function getFlashSaleProducts(int $limit = 12): Collection;

    /**
     * 根據分類取得特價商品
     */
    public function getOnSaleProductsByCategory(int $categoryId): Collection;

    /**
     * 取得特價商品數量
     */
    public function getOnSaleProductsCount(): int;
}
