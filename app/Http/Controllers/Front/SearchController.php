<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Services\Product\SearchService;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    protected $searchService;

    public function __construct(SearchService $searchService)
    {
        $this->searchService = $searchService;
    }

    /**
     * 搜尋商品
     * GET /api/search?q=關鍵字
     */
    public function search(Request $request)
    {
        $query = $request->input('q', '');
        $limit = min((int) $request->input('limit', 10), 50);

        if (empty(trim($query))) {
            return response()->json([
                'success' => true,
                'data' => [],
                'message' => '請輸入搜尋關鍵字'
            ]);
        }

        try {
            $products = $this->searchService->search($query, $limit);

            return response()->json([
                'success' => true,
                'data' => $products,
                'count' => count($products),
                'query' => $query
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '搜尋發生錯誤',
                'data' => []
            ], 500);
        }
    }
}
