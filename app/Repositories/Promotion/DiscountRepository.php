<?php

namespace App\Repositories\Promotion;

use App\Repositories\Contracts\Promotion\DiscountRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DiscountRepository implements DiscountRepositoryInterface
{
    /**
     * 取得所有特價商品
     */
    public function getOnSaleProducts(): Collection
    {
        return DB::table('products')
            ->join('brands', 'products.brand_id', '=', 'brands.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->select(
                'products.id',
                'products.name',
                'products.description',
                DB::raw('CAST(products.price AS DECIMAL(10,2)) as price'),
                DB::raw('CAST(products.original_price AS DECIMAL(10,2)) as original_price'),
                'products.image',
                'products.stock',
                'brands.name as brand',
                'categories.name as category',
                'categories.id as category_id'
            )
            ->where('products.is_active', true)
            ->where('products.stock', '>', 0)
            ->whereNotNull('products.original_price')
            ->whereRaw('products.original_price > products.price')
            ->orderBy('products.id', 'desc')
            ->get();
    }

    /**
     * 取得限時優惠商品（有時間限制的特價）
     */
    public function getFlashSaleProducts(int $limit = 12): Collection
    {
        return DB::table('products')
            ->join('brands', 'products.brand_id', '=', 'brands.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->select(
                'products.id',
                'products.name',
                'products.description',
                DB::raw('CAST(products.price AS DECIMAL(10,2)) as price'),
                DB::raw('CAST(products.original_price AS DECIMAL(10,2)) as original_price'),
                'products.image',
                'products.stock',
                'brands.name as brand',
                'categories.name as category',
                'categories.id as category_id'
            )
            ->where('products.is_active', true)
            ->where('products.stock', '>', 0)
            ->whereNotNull('products.original_price')
            ->whereRaw('products.original_price > products.price')
            ->orderBy('products.id', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * 根據分類取得特價商品
     */
    public function getOnSaleProductsByCategory(int $categoryId): Collection
    {
        return DB::table('products')
            ->join('brands', 'products.brand_id', '=', 'brands.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->select(
                'products.id',
                'products.name',
                'products.description',
                DB::raw('CAST(products.price AS DECIMAL(10,2)) as price'),
                DB::raw('CAST(products.original_price AS DECIMAL(10,2)) as original_price'),
                'products.image',
                'products.stock',
                'brands.name as brand',
                'categories.name as category',
                'categories.id as category_id'
            )
            ->where('products.is_active', true)
            ->where('products.stock', '>', 0)
            ->where('products.category_id', $categoryId)
            ->whereNotNull('products.original_price')
            ->whereRaw('products.original_price > products.price')
            ->orderBy('products.id', 'desc')
            ->get();
    }

    /**
     * 取得特價商品數量
     */
    public function getOnSaleProductsCount(): int
    {
        return DB::table('products')
            ->where('is_active', true)
            ->where('stock', '>', 0)
            ->whereNotNull('original_price')
            ->whereRaw('original_price > price')
            ->count();
    }
}
