<?php

namespace App\Repositories\Product;

use App\Models\Product\Product;
use App\Repositories\Contracts\Product\SearchRepositoryInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SearchRepository implements SearchRepositoryInterface
{
    protected $productModel;
    protected $openSearchUrl;
    protected $openSearchIndex;

    public function __construct(Product $productModel)
    {
        $this->productModel = $productModel;
        $this->openSearchUrl = config('services.opensearch.url', 'http://localhost:9200');
        $this->openSearchIndex = config('services.opensearch.index', 'products');
    }

    /**
     * 使用 OpenSearch 搜尋，回傳 product_id 陣列
     */
    public function searchWithOpenSearch(string $query, int $limit): array
    {
        $searchBody = [
            'query' => [
                'multi_match' => [
                    'query' => $query,
                    'fields' => ['name^3', 'description', 'brand_name', 'category_name'],
                    'type' => 'best_fields',
                    'fuzziness' => 'AUTO',
                    'prefix_length' => 1
                ]
            ],
            'size' => $limit,
            '_source' => ['product_id']
        ];

        $response = Http::timeout(5)->post(
            "{$this->openSearchUrl}/{$this->openSearchIndex}/_search",
            $searchBody
        );

        if (!$response->successful()) {
            throw new \Exception('OpenSearch 回應錯誤: ' . $response->status());
        }

        $data = $response->json();
        $productIds = [];

        if (isset($data['hits']['hits'])) {
            foreach ($data['hits']['hits'] as $hit) {
                $productIds[] = $hit['_source']['product_id'] ?? $hit['_id'];
            }
        }

        return $productIds;
    }

    /**
     * 使用資料庫模糊搜尋
     */
    public function searchWithDatabase(string $query, int $limit): array
    {
        $searchTerm = '%' . $query . '%';

        $products = $this->productModel->newQuery()
            ->with(['category', 'brand'])
            ->where('is_active', true)
            ->where(function ($q) use ($searchTerm) {
                $q->where('name', 'ILIKE', $searchTerm)
                  ->orWhere('description', 'ILIKE', $searchTerm)
                  ->orWhereHas('brand', function ($bq) use ($searchTerm) {
                      $bq->where('name', 'ILIKE', $searchTerm);
                  })
                  ->orWhereHas('category', function ($cq) use ($searchTerm) {
                      $cq->where('name', 'ILIKE', $searchTerm);
                  });
            })
            ->orderByRaw("
                CASE
                    WHEN name ILIKE ? THEN 1
                    WHEN name ILIKE ? THEN 2
                    ELSE 3
                END
            ", [$query . '%', '%' . $query . '%'])
            ->limit($limit)
            ->get();

        return $products->toArray();
    }

    /**
     * 根據 ID 陣列取得商品
     */
    public function getProductsByIds(array $productIds): array
    {
        if (empty($productIds)) {
            return [];
        }

        $products = $this->productModel->newQuery()
            ->with(['category', 'brand'])
            ->whereIn('id', $productIds)
            ->where('is_active', true)
            ->get();

        // 保持 OpenSearch 返回的排序
        $productsById = $products->keyBy('id');
        $sortedProducts = [];

        foreach ($productIds as $id) {
            if (isset($productsById[$id])) {
                $sortedProducts[] = $productsById[$id]->toArray();
            }
        }

        return $sortedProducts;
    }
}
