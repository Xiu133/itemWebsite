<?php

namespace App\Repositories\Contracts\Product;

interface CategoryRepositoryInterface
{
    public function getAll();

    public function getAllActive();

    public function findById($id);

    public function create(array $data);

    public function update($id, array $data);

    public function delete($id);

    public function forceDelete($id);

    public function restore($id);

    public function getTrashed();

    public function getWithProductCount();
}
