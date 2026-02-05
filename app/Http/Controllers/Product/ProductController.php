<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Services\Product\ProductsServices;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class ProductController extends Controller
{
    protected $productsService;

    public function __construct(ProductsServices $productsService)
    {
        $this->productsService = $productsService;
    }

    /**
     * 取得商品列表（支援分頁與篩選）
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 15);
            $filters = $request->only(['category_id', 'brand_id', 'is_active', 'search']);

            $products = $this->productsService->getPaginatedProducts($perPage, $filters);

            return response()->json([
                'success' => true,
                'data' => $products->items(),
                'meta' => [
                    'current_page' => $products->currentPage(),
                    'last_page' => $products->lastPage(),
                    'per_page' => $products->perPage(),
                    'total' => $products->total(),
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve products',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 取得表單所需資料（分類、品牌、標籤）
     */
    public function formData(): JsonResponse
    {
        try {
            $data = $this->productsService->getFormData();

            return response()->json([
                'success' => true,
                'data' => $data
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve form data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 新增商品
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $product = $this->productsService->createProduct($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Product created successfully',
                'data' => $product
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create product',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 取得單一商品詳情
     */
    public function show($id): JsonResponse
    {
        try {
            $product = $this->productsService->getProductById($id);

            return response()->json([
                'success' => true,
                'data' => $product
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * 更新商品
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $product = $this->productsService->updateProduct($id, $request->all());

            return response()->json([
                'success' => true,
                'message' => 'Product updated successfully',
                'data' => $product
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update product',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * 軟刪除商品
     */
    public function destroy($id): JsonResponse
    {
        try {
            $this->productsService->deleteProduct($id);

            return response()->json([
                'success' => true,
                'message' => 'Product soft deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete product',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * 永久刪除商品
     */
    public function forceDestroy($id): JsonResponse
    {
        try {
            $this->productsService->forceDeleteProduct($id);

            return response()->json([
                'success' => true,
                'message' => 'Product permanently deleted'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to permanently delete product',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * 還原已刪除的商品
     */
    public function restore($id): JsonResponse
    {
        try {
            $this->productsService->restoreProduct($id);

            return response()->json([
                'success' => true,
                'message' => 'Product restored successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to restore product',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * 取得已刪除的商品列表
     */
    public function trashed(): JsonResponse
    {
        try {
            $products = $this->productsService->getTrashedProducts();

            return response()->json([
                'success' => true,
                'data' => $products
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve trashed products',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
