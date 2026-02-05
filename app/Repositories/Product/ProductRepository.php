<?php

namespace App\Repositories\Product;

use App\Models\Product\Product;
use App\Repositories\Contracts\Product\ProductRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class ProductRepository implements ProductRepositoryInterface
{
    protected $model;

    public function __construct(Product $model)
    {
        $this->model = $model;
    }

    public function getAll(): Collection
    {
        return $this->model->all();
    }

    public function getAllWithRelations(): Collection
    {
        return $this->model->with(['category', 'brand', 'tags'])->get();
    }

    public function paginate(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $query = $this->model->with(['category', 'brand', 'tags']);

        if (!empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (!empty($filters['brand_id'])) {
            $query->where('brand_id', $filters['brand_id']);
        }

        if (!empty($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('description', 'like', '%' . $filters['search'] . '%');
            });
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    public function findById($id): ?Product
    {
        return $this->model->findOrFail($id);
    }

    public function findByIdWithRelations($id): ?Product
    {
        return $this->model->with(['category', 'brand', 'tags'])->findOrFail($id);
    }

    public function create(array $data): Product
    {
        return $this->model->create($data);
    }

    public function update($id, array $data): Product
    {
        $product = $this->findById($id);
        $product->update($data);
        return $product->fresh(['category', 'brand', 'tags']);
    }

    public function syncTags($id, array $tagIds): void
    {
        $product = $this->findById($id);
        $product->tags()->sync($tagIds);
    }

    public function delete($id): bool
    {
        $product = $this->findById($id);
        return $product->delete();
    }

    public function forceDelete($id): bool
    {
        $product = $this->model->withTrashed()->findOrFail($id);
        return $product->forceDelete();
    }

    public function restore($id): bool
    {
        $product = $this->model->withTrashed()->findOrFail($id);
        return $product->restore();
    }

    public function getTrashed(): Collection
    {
        return $this->model->onlyTrashed()->with(['category', 'brand', 'tags'])->get();
    }
}
