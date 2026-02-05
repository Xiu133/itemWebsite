<?php

namespace App\Http\Controllers\Promotion;

use App\Http\Controllers\Controller;
use App\Services\Promotion\DiscountService;
use Illuminate\Http\Request;

class DiscountController extends Controller
{
    protected $discountService;

    public function __construct(DiscountService $discountService)
    {
        $this->discountService = $discountService;
    }

    /**
     * 顯示限時優惠頁面
     */
    public function index()
    {
        $products = $this->discountService->getAllOnSaleProducts();
        $categories = $this->discountService->getCategoriesWithSaleProducts();
        $totalCount = $this->discountService->getOnSaleProductsCount();

        return view('discount.index', [
            'products' => $products,
            'categories' => $categories,
            'totalCount' => $totalCount
        ]);
    }

    /**
     * API: 取得特價商品
     */
    public function getProducts(Request $request)
    {
        $categoryId = $request->query('category');

        if ($categoryId) {
            $products = $this->discountService->getOnSaleProductsByCategory((int)$categoryId);
        } else {
            $products = $this->discountService->getAllOnSaleProducts();
        }

        return response()->json([
            'success' => true,
            'data' => $products
        ]);
    }
}
