<?php

namespace App\Services\Product;

use App\Models\Front\Brand;
use App\Models\Product\Category;
use App\Models\Product\Tag;
use App\Repositories\Contracts\Product\ProductRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ProductsServices
{
    protected $productRepository;

    public function __construct(ProductRepositoryInterface $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    public function getAllProducts()
    {
        return $this->productRepository->getAllWithRelations();
    }

    public function getPaginatedProducts(int $perPage = 15, array $filters = [])
    {
        return $this->productRepository->paginate($perPage, $filters);
    }

    public function getProductById($id)
    {
        return $this->productRepository->findByIdWithRelations($id);
    }

    public function getFormData(): array
    {
        return [
            'categories' => Category::where('is_active', true)
                ->orderBy('sort_order')
                ->get(['id', 'name']),
            'brands' => Brand::orderBy('name')->get(['id', 'name', 'logo']),
            'tags' => Tag::where('is_active', true)
                ->orderBy('sort_order')
                ->get(['id', 'name', 'color', 'icon']),
        ];
    }

    public function createProduct(array $data)
    {
        $this->validateProduct($data);

        return DB::transaction(function () use ($data) {
            $tagIds = $data['tag_ids'] ?? [];
            unset($data['tag_ids']);

            $product = $this->productRepository->create($data);

            if (!empty($tagIds)) {
                $this->productRepository->syncTags($product->id, $tagIds);
            }

            return $this->productRepository->findByIdWithRelations($product->id);
        });
    }

    public function updateProduct($id, array $data)
    {
        $this->validateProduct($data, $id);

        return DB::transaction(function () use ($id, $data) {
            $tagIds = $data['tag_ids'] ?? [];
            unset($data['tag_ids']);

            $product = $this->productRepository->update($id, $data);

            $this->productRepository->syncTags($id, $tagIds);

            return $this->productRepository->findByIdWithRelations($id);
        });
    }

    public function deleteProduct($id)
    {
        return $this->productRepository->delete($id);
    }

    public function forceDeleteProduct($id)
    {
        return $this->productRepository->forceDelete($id);
    }

    public function restoreProduct($id)
    {
        return $this->productRepository->restore($id);
    }

    public function getTrashedProducts()
    {
        return $this->productRepository->getTrashed();
    }

    protected function validateProduct(array $data, $id = null)
    {
        $rules = [
            'category_id' => 'required|exists:categories,id',
            'brand_id' => 'required|exists:brands,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'original_price' => 'nullable|numeric|min:0',
            'image' => 'nullable|string',
            'stock' => 'required|integer|min:0',
            'is_active' => 'boolean',
            'tag_ids' => 'nullable|array',
            'tag_ids.*' => 'exists:tags,id',
        ];

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }
}