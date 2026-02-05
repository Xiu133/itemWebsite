<?php

namespace App\Services\Product;

use App\Repositories\Contracts\Product\SearchRepositoryInterface;
use Illuminate\Support\Facades\Log;

class SearchService
{
    protected $searchRepository;

    public function __construct(SearchRepositoryInterface $searchRepository)
    {
        $this->searchRepository = $searchRepository;
    }

    /**
     * 模糊搜尋商品
     * 流程: 搜尋關鍵字 -> OpenSearch -> 取得 product_id -> 從 Product table 取出商品
     */
    public function search(string $query, int $limit = 10): array
    {
        if (empty(trim($query))) {
            return [];
        }

        try {
            // 嘗試使用 OpenSearch 搜尋
            $productIds = $this->searchRepository->searchWithOpenSearch($query, $limit);

            if (!empty($productIds)) {
                $products = $this->searchRepository->getProductsByIds($productIds);
                return array_map([$this, 'formatProduct'], $products);
            }
        } catch (\Exception $e) {
            Log::warning('OpenSearch 搜尋失敗，改用資料庫搜尋: ' . $e->getMessage());
        }

        // Fallback: 使用資料庫模糊搜尋
        $products = $this->searchRepository->searchWithDatabase($query, $limit);
        return array_map([$this, 'formatProduct'], $products);
    }

    /**
     * 格式化商品資料供前端使用
     */
    protected function formatProduct(array $product): array
    {
        $originalPrice = $product['original_price'] ?? null;
        $price = (float) $product['price'];
        $onSale = $originalPrice && (float) $originalPrice > $price;

        $image = $product['image'] ?? null;
        if ($image && !str_starts_with($image, '/')) {
            $image = '/images/' . $image;
        }

        return [
            'id' => $product['id'],
            'name' => $product['name'],
            'description' => $product['description'] ?? '',
            'price' => $price,
            'original_price' => $originalPrice ? (float) $originalPrice : null,
            'image' => $image,
            'brand' => isset($product['brand']) ? $product['brand']['name'] : null,
            'category' => isset($product['category']) ? $product['category']['name'] : null,
            'category_id' => $product['category_id'] ?? null,
            'on_sale' => $onSale,
        ];
    }
}
