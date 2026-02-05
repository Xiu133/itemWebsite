<?php

namespace App\Services\Product;

use App\Models\Product\Product;
use App\Repositories\Contracts\Product\ProductManageRepositoryInterface;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ProductManageService
{
    protected $repository;

    public function __construct(ProductManageRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    protected function logAction(string $action, Product $product, ?array $changes = null): void
    {
        activity('product')
            ->performedOn($product)
            ->withProperties([
                'action' => $action,
                'product_name' => $product->name,
                'changes' => $changes,
            ])
            ->log($this->getActionDescription($action, $product->name));
    }

    protected function getActionDescription(string $action, string $productName): string
    {
        return match ($action) {
            'create' => "新增商品: {$productName}",
            'update' => "更新商品: {$productName}",
            'delete' => "刪除商品: {$productName}",
            'toggle_status' => "切換商品狀態: {$productName}",
            default => "{$action}: {$productName}",
        };
    }

    public function getUserProducts(int $userId)
    {
        $products = $this->repository->getByUserId($userId);

        return $products->map(function ($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'price' => (float) $product->price,
                'stock' => $product->stock,
                'is_active' => $product->is_active,
                'image' => $product->image ? '/images/' . $product->image : null,
                'created_at' => $product->created_at->format('Y-m-d'),
            ];
        });
    }

    public function getFormData(): array
    {
        return [
            'categories' => $this->repository->getCategories(),
            'brands' => $this->repository->getBrands(),
        ];
    }

    public function getProductForEdit(int $id, int $userId)
    {
        return $this->repository->findByIdAndUserId($id, $userId);
    }

    public function createProduct(array $data, int $userId)
    {
        $this->validateProduct($data);

        $productData = [
            'user_id' => $userId,
            'category_id' => $data['category_id'],
            'brand_id' => $data['brand_id'],
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'price' => $data['price'],
            'original_price' => $data['original_price'] ?? $data['price'],
            'stock' => $data['stock'],
            'image' => $data['image'] ?? null,
            'is_active' => $data['is_active'] ?? true,
        ];

        $product = $this->repository->create($productData);

        $this->logAction('create', $product, $productData);

        return $product;
    }

    public function updateProduct(int $id, array $data, int $userId)
    {
        $product = $this->repository->findByIdAndUserId($id, $userId);
        $oldData = $product->toArray();

        $this->validateProduct($data);

        $updateData = [
            'category_id' => $data['category_id'],
            'brand_id' => $data['brand_id'],
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'price' => $data['price'],
            'original_price' => $data['original_price'] ?? $data['price'],
            'stock' => $data['stock'],
            'is_active' => $data['is_active'] ?? true,
        ];

        if (isset($data['image'])) {
            $updateData['image'] = $data['image'];
        }

        $updatedProduct = $this->repository->update($id, $updateData);

        $this->logAction('update', $updatedProduct, [
            'old' => $oldData,
            'new' => $updateData,
        ]);

        return $updatedProduct;
    }

    public function deleteProduct(int $id, int $userId): bool
    {
        $product = $this->repository->findByIdAndUserId($id, $userId);
        $productData = $product->toArray();

        // Log before delete since we need the product object
        $this->logAction('delete', $product, $productData);

        $result = $this->repository->delete($id);

        return $result;
    }

    public function toggleProductStatus(int $id, bool $isActive): bool
    {
        $product = $this->repository->update($id, ['is_active' => $isActive]);

        $this->logAction('toggle_status', $product, [
            'is_active' => $isActive,
        ]);

        return true;
    }

    public function handleImageUpload($file): ?string
    {
        if (!$file) {
            return null;
        }

        $filename = time() . '_' . $file->getClientOriginalName();
        $file->move(public_path('images'), $filename);
        return $filename;
    }

    protected function validateProduct(array $data): void
    {
        $rules = [
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'brand_id' => 'required|exists:brands,id',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'original_price' => 'nullable|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'is_active' => 'boolean',
        ];

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }
}
