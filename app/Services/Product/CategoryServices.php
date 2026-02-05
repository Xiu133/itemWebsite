<?php

namespace App\Services\Product;

use App\Repositories\Contracts\Product\CategoryRepositoryInterface;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class CategoryServices
{
    protected $categoryRepository;

    public function __construct(CategoryRepositoryInterface $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }

    public function getAllCategories()
    {
        return $this->categoryRepository->getAll();
    }

    public function getActiveCategories()
    {
        return $this->categoryRepository->getAllActive();
    }

    public function getCategoriesWithProductCount()
    {
        return $this->categoryRepository->getWithProductCount();
    }

    public function getCategoryById($id)
    {
        return $this->categoryRepository->findById($id);
    }

    public function createCategory(array $data)
    {
        $this->validateCategory($data);
        return $this->categoryRepository->create($data);
    }

    public function updateCategory($id, array $data)
    {
        $this->validateCategory($data, $id);
        return $this->categoryRepository->update($id, $data);
    }

    public function deleteCategory($id)
    {
        return $this->categoryRepository->delete($id);
    }

    public function forceDeleteCategory($id)
    {
        return $this->categoryRepository->forceDelete($id);
    }

    public function restoreCategory($id)
    {
        return $this->categoryRepository->restore($id);
    }

    public function getTrashedCategories()
    {
        return $this->categoryRepository->getTrashed();
    }

    protected function validateCategory(array $data, $id = null)
    {
        $rules = [
            'name' => 'required|string|max:255',
            'image' => 'nullable|string',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean'
        ];

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }
}