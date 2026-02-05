<?php

namespace App\Repositories\Contracts\Product;

use Illuminate\Database\Eloquent\Collection;

interface ProductManageRepositoryInterface
{
    public function getByUserId(int $userId): Collection;

    public function findByIdAndUserId(int $id, int $userId);

    public function create(array $data);

    public function update(int $id, array $data);

    public function delete(int $id): bool;

    public function getCategories(): Collection;

    public function getBrands(): Collection;
}
