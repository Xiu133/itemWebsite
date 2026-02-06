<?php

namespace App\Repositories\Contracts\Product;

use App\Models\Product\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface ProductRepositoryInterface
{
    public function getAll(): Collection;

    public function getAllWithRelations(): Collection;

    public function paginate(int $perPage = 15, array $filters = []): LengthAwarePaginator;

    public function findById($id): ?Product;

    public function findByIdWithRelations($id): ?Product;

    public function create(array $data): Product;

    public function update($id, array $data): Product;

    public function syncTags($id, array $tagIds): void;

    public function delete($id): bool;

    public function forceDelete($id): bool;

    public function restore($id): bool;

    public function getTrashed(): Collection;
}
