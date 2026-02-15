<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function index()
    {
        // 取得分類及其上架商品數量（1 次查詢，用 LEFT JOIN + COUNT 取代 N+1 迴圈）
        $categories = DB::table('categories')
            ->leftJoin('products', function ($join) {
                $join->on('categories.id', '=', 'products.category_id')
                     ->where('products.is_active', true)
                     ->whereNull('products.deleted_at');
            })
            ->where('categories.is_active', true)
            ->groupBy('categories.id', 'categories.name', 'categories.slug', 'categories.image', 'categories.sort_order')
            ->orderBy('categories.sort_order')
            ->select(
                'categories.id',
                'categories.name',
                'categories.slug',
                'categories.image',
                DB::raw('COUNT(products.id) as count')
            )
            ->get()
            ->map(function ($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'image' => $category->image ? '/images/' . $category->image : null,
                    'count' => (int) $category->count
                ];
            });

        // 取得熱門商品 + 標籤（用 LEFT JOIN 一次取得，取代 N+1 迴圈查 tag）
        $products = DB::table('products')
            ->join('brands', 'products.brand_id', '=', 'brands.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->leftJoin(DB::raw('(SELECT DISTINCT ON (product_id) product_id, tag_id FROM product_tag ORDER BY product_id, id) AS first_tag'), 'products.id', '=', 'first_tag.product_id')
            ->leftJoin('tags', 'first_tag.tag_id', '=', 'tags.id')
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
                'tags.name as tag_name',
                'tags.slug as tag_slug',
                'tags.color as tag_color'
            )
            ->where('products.is_active', true)
            ->whereNull('products.deleted_at')
            ->where('products.stock', '>', 0)
            ->orderBy('products.id', 'desc')
            ->limit(12)
            ->get()
            ->map(function ($product) {
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
                    'image' => $product->image ? '/images/' . $product->image : null,
                    'tag' => $product->tag_name,
                    'tagType' => $product->tag_slug ?? '',
                    'tagColor' => $product->tag_color,
                    'stock' => $product->stock,
                    'category' => $product->category
                ];
            });

        return view('ecommerce.index', [
            'categories' => $categories,
            'products' => $products
        ]);
    }
}
