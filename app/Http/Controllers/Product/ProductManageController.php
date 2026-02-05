<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Models\Product\Product;
use App\Services\Product\ProductManageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductManageController extends Controller
{
    protected $service;

    public function __construct(ProductManageService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $products = $this->service->getUserProducts(Auth::id());

        return view('products.manage.index', compact('products'));
    }

    public function create()
    {
        $formData = $this->service->getFormData();

        return view('products.manage.create', [
            'categories' => $formData['categories'],
            'brands' => $formData['brands'],
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->all();

        if ($request->hasFile('image')) {
            $data['image'] = $this->service->handleImageUpload($request->file('image'));
        }

        $data['is_active'] = $request->boolean('is_active', true);

        $this->service->createProduct($data, Auth::id());

        return redirect()->route('my-products.index')->with('success', '商品上架成功！');
    }

    public function edit(Product $product)
    {
        $formData = $this->service->getFormData();

        return view('products.manage.edit', [
            'product' => $product,
            'categories' => $formData['categories'],
            'brands' => $formData['brands'],
        ]);
    }

    public function update(Request $request, Product $product)
    {
        $data = $request->all();

        if ($request->hasFile('image')) {
            $data['image'] = $this->service->handleImageUpload($request->file('image'));
        }

        $data['is_active'] = $request->boolean('is_active', true);

        $this->service->updateProduct($product->id, $data, Auth::id());

        return redirect()->route('my-products.index')->with('success', '商品更新成功！');
    }

    public function delete(Product $product)
    {
        return view('products.manage.delete', compact('product'));
    }

    public function destroy(Product $product)
    {
        $this->service->deleteProduct($product->id, Auth::id());

        return redirect()->route('my-products.index')->with('success', '商品已刪除');
    }

    public function toggleStatus(Request $request, Product $product)
    {
        $isActive = $request->boolean('is_active');
        $this->service->toggleProductStatus($product->id, $isActive);

        return response()->json(['success' => true]);
    }
}
