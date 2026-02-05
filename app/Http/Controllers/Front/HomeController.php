<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function index()
    {
        // 获取分类数据及其商品数量
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
                    'image' => $category->image ? '/images/' . $category->image : null,
                    'count' => $count
                ];
            });

        // 获取热门商品（带品牌信息）
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
                'brands.name as brand',
                'categories.name as category'
            )
            ->where('products.is_active', true)
            ->where('products.stock', '>', 0)
            ->orderBy('products.id', 'desc')
            ->limit(12)
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

                // 只有當原價大於售價時才顯示原價（表示有折扣）
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
                    'tag' => $tagName,
                    'tagType' => $tagType,
                    'tagColor' => $tag->color ?? null,
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
