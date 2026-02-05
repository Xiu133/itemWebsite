<?php

namespace App\Services\Promotion;

use App\Repositories\Contracts\Promotion\DiscountRepositoryInterface;
use Illuminate\Support\Collection;

class DiscountService
{
    protected $discountRepository;

    public function __construct(DiscountRepositoryInterface $discountRepository)
    {
        $this->discountRepository = $discountRepository;
    }

    /**
     * 取得所有特價商品並格式化
     */
    public function getAllOnSaleProducts(): Collection
    {
        $products = $this->discountRepository->getOnSaleProducts();
        return $this->formatProducts($products);
    }

    /**
     * 取得限時優惠商品
     */
    public function getFlashSaleProducts(int $limit = 12): Collection
    {
        $products = $this->discountRepository->getFlashSaleProducts($limit);
        return $this->formatProducts($products);
    }

    /**
     * 根據分類取得特價商品
     */
    public function getOnSaleProductsByCategory(int $categoryId): Collection
    {
        $products = $this->discountRepository->getOnSaleProductsByCategory($categoryId);
        return $this->formatProducts($products);
    }

    /**
     * 取得特價商品數量
     */
    public function getOnSaleProductsCount(): int
    {
        return $this->discountRepository->getOnSaleProductsCount();
    }

    /**
     * 取得所有有特價商品的分類
     */
    public function getCategoriesWithSaleProducts(): Collection
    {
        $products = $this->discountRepository->getOnSaleProducts();

        return $products->groupBy('category_id')->map(function ($items, $categoryId) {
            $first = $items->first();
            return [
                'id' => $categoryId,
                'name' => $first->category,
                'count' => $items->count()
            ];
        })->values();
    }

    /**
     * 格式化商品資料
     */
    protected function formatProducts(Collection $products): Collection
    {
        return $products->map(function ($product) {
            $originalPrice = null;
            $discountPercent = null;

            if ($product->original_price && (float)$product->original_price > (float)$product->price) {
                $originalPrice = (float)$product->original_price;
                $discountPercent = round((1 - (float)$product->price / $originalPrice) * 100);
            }

            return [
                'id' => $product->id,
                'brand' => $product->brand,
                'name' => $product->name,
                'description' => $product->description,
                'price' => (float)$product->price,
                'originalPrice' => $originalPrice,
                'discountPercent' => $discountPercent,
                'image' => $product->image ? '/images/' . $product->image : null,
                'tag' => '特價',
                'tagType' => 'sale',
                'stock' => $product->stock,
                'category' => $product->category,
                'categoryId' => $product->category_id
            ];
        });
    }
}
