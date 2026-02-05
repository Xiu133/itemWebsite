<?php

namespace App\Repositories\Contracts\Product;

interface SearchRepositoryInterface
{
    /**
     * 使用 OpenSearch 搜尋，回傳 product_id 陣列
     */
    public function searchWithOpenSearch(string $query, int $limit): array;

    /**
     * 使用資料庫模糊搜尋
     */
    public function searchWithDatabase(string $query, int $limit): array;

    /**
     * 根據 ID 陣列取得商品
     */
    public function getProductsByIds(array $productIds): array;
}
