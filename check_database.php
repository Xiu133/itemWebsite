<?php

/**
 * 資料庫檢查腳本
 * 檢查 brands 和 products 表的資料狀態
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== 資料庫狀態檢查 ===\n\n";

// 檢查 brands 表
echo "1. 品牌表 (brands) 資料數量: ";
$brandCount = DB::table('brands')->count();
echo $brandCount . "\n";

if ($brandCount > 0) {
    echo "   品牌列表:\n";
    $brands = DB::table('brands')->get(['id', 'name']);
    foreach ($brands as $brand) {
        echo "   - [{$brand->id}] {$brand->name}\n";
    }
} else {
    echo "   ⚠️  警告: 品牌表是空的！\n";
}

echo "\n";

// 檢查 products 表
echo "2. 產品表 (products) 資料數量: ";
$productCount = DB::table('products')->count();
echo $productCount . "\n";

if ($productCount > 0) {
    echo "   檢查前 5 個產品的 brand_id:\n";
    $products = DB::table('products')
        ->select('id', 'name', 'brand_id', 'category_id')
        ->limit(5)
        ->get();

    foreach ($products as $product) {
        $brandName = DB::table('brands')->where('id', $product->brand_id)->value('name');
        echo "   - [{$product->id}] {$product->name}\n";
        echo "     brand_id: {$product->brand_id} => " . ($brandName ?? '找不到品牌') . "\n";
    }

    // 檢查是否有產品的 brand_id 無效
    $invalidBrandProducts = DB::table('products')
        ->leftJoin('brands', 'products.brand_id', '=', 'brands.id')
        ->whereNull('brands.id')
        ->count();

    if ($invalidBrandProducts > 0) {
        echo "\n   ⚠️  警告: 有 {$invalidBrandProducts} 個產品的 brand_id 無效（找不到對應的品牌）\n";
    } else {
        echo "\n   ✅ 所有產品的 brand_id 都有效\n";
    }
} else {
    echo "   ⚠️  警告: 產品表是空的！\n";
}

echo "\n";

// 檢查 categories 表
echo "3. 分類表 (categories) 資料數量: ";
$categoryCount = DB::table('categories')->count();
echo $categoryCount . "\n";

if ($categoryCount > 0) {
    echo "   分類列表:\n";
    $categories = DB::table('categories')->get(['id', 'name']);
    foreach ($categories as $category) {
        echo "   - [{$category->id}] {$category->name}\n";
    }
} else {
    echo "   ⚠️  警告: 分類表是空的！\n";
}

echo "\n=== 檢查完成 ===\n";

// 提供建議
if ($brandCount == 0 || $productCount == 0 || $categoryCount == 0) {
    echo "\n建議執行以下指令來填充資料:\n";
    echo "php artisan db:seed\n";
    echo "\n或分別執行:\n";
    if ($categoryCount == 0) echo "php artisan db:seed --class=CategorySeeder\n";
    if ($brandCount == 0) echo "php artisan db:seed --class=BrandSeeder\n";
    if ($productCount == 0) echo "php artisan db:seed --class=ProductSeeder\n";
}
