<?php

namespace App\Repositories\Product;

use App\Models\Product\Category;
use App\Repositories\Contracts\Product\CategoryRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class CategoryRepository implements CategoryRepositoryInterface
{
    protected $model;

    public function __construct(Category $model)
    {
        $this->model = $model;
    }

    public function getAll(): Collection
    {
        return $this->model->orderBy('sort_order')->get();
    }

    public function getAllActive(): Collection
    {
        return $this->model->where('is_active', true)
            ->orderBy('sort_order')
            ->get();
    }

    public function findById($id): ?Category
    {
        return $this->model->findOrFail($id);
    }

    public function create(array $data): Category
    {
        return $this->model->create($data);
    }

    public function update($id, array $data): Category
    {
        $category = $this->findById($id);
        $category->update($data);
        return $category;
    }

    public function delete($id): bool
    {
        $category = $this->findById($id);
        return $category->delete();
    }

    public function forceDelete($id): bool
    {
        $category = $this->model->withTrashed()->findOrFail($id);
        return $category->forceDelete();
    }

    public function restore($id): bool
    {
        $category = $this->model->withTrashed()->findOrFail($id);
        return $category->restore();
    }

    public function getTrashed(): Collection
    {
        return $this->model->onlyTrashed()->get();
    }

    public function getWithProductCount(): Collection
    {
        return $this->model->withCount('products')
            ->orderBy('sort_order')
            ->get();
    }
}
