<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CommodityController extends Controller
{
    public function index()
    {
        // 獲取所有品牌
        $brands = DB::table('brands')
            ->where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(function ($brand) {
                $count = DB::table('products')
                    ->where('brand_id', $brand->id)
                    ->where('is_active', true)
                    ->count();

                return [
                    'id' => $brand->id,
                    'name' => $brand->name,
                    'count' => $count
                ];
            });

        // 獲取所有分類
        $categories = DB::table('categories')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->map(function ($category) {
                $count = DB::table('products')
                    ->where('category_id', $category->id)
                    ->where('is_active', true)
                    ->count();

                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'image' => $category->image,
                    'count' => $count
                ];
            });

        // 獲取精選商品（帶品牌信息）
        $products = DB::table('products')
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
                'products.category_id',
                'products.brand_id',
                'brands.name as brand',
                'categories.name as category'
            )
            ->where('products.is_active', true)
            ->where('products.stock', '>', 0)
            ->orderBy('products.id', 'desc')
            ->get()
            ->map(function ($product) {
                // 從關聯表取得標籤
                $tag = DB::table('product_tag')
                    ->join('tags', 'product_tag.tag_id', '=', 'tags.id')
                    ->where('product_tag.product_id', $product->id)
                    ->select('tags.name', 'tags.slug', 'tags.color')
                    ->first();

                $tagName = $tag->name ?? null;
                $tagType = $tag->slug ?? '';

                $originalPrice = null;
                if ($product->original_price && (float)$product->original_price > (float)$product->price) {
                    $originalPrice = (float) $product->original_price;
                }

                return [
                    'id' => $product->id,
                    'brand' => $product->brand,
                    'name' => $product->name,
                    'description' => $product->description,
                    'price' => (float) $product->price,
                    'originalPrice' => $originalPrice,
                    'image' => $product->image,
                    'tag' => $tagName,
                    'tagType' => $tagType,
                    'tagColor' => $tag->color ?? null,
                    'stock' => $product->stock,
                    'category' => $product->category,
                    'category_id' => $product->category_id,
                    'brand_id' => $product->brand_id
                ];
            });

        return view('commodity.index', [
            'categories' => $categories,
            'brands' => $brands,
            'products' => $products
        ]);
    }
}
