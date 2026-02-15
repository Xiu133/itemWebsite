<?php

namespace App\Repositories\Product;

use App\Models\Product\Product;
use App\Models\Product\Category;
use App\Models\Front\Brand;
use App\Repositories\Contracts\Product\ProductManageRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class ProductManageRepository implements ProductManageRepositoryInterface
{
    protected $model;

    public function __construct(Product $model)
    {
        $this->model = $model;
    }

    public function getByUserId(int $userId): Collection
    {
        return $this->model
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function findByIdAndUserId(int $id, int $userId)
    {
        return $this->model
            ->where('id', $id)
            ->where('user_id', $userId)
            ->firstOrFail();
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data)
    {
        $product = $this->model->findOrFail($id);
        $product->update($data);
        return $product->fresh();
    }

    public function delete(int $id): bool
    {
        $product = $this->model->findOrFail($id);
        return $product->delete();
    }

    public function getCategories(): Collection
    {
        return Category::where('is_active', true)
            ->orderBy('sort_order')
            ->get();
    }

    public function getBrands(): Collection
    {
        return Brand::where('is_active', true)
            ->orderBy('name')
            ->get();
    }
}
