<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductRequest;
use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProductController extends Controller
{
    public function __construct(public ProductService $productService) {}

    public function index(Request $request)
    {
        $search = (string) $request->input('search', '');
        $sortBy = in_array($request->input('sort_column'), ['id', 'name_ar', 'name_en', 'slug', 'ordering', 'created_at']) ? $request->input('sort_column') : 'ordering';
        $sortDir = $request->input('sort_direction', 'asc') === 'desc' ? 'desc' : 'asc';

        $products = $this->productService->getPaginatedProducts(search: $search, sortBy: $sortBy, sortDir: $sortDir);

        return Inertia::render('Products/ProductsPage', [
            'products' => $products,
            'filters' => [
                'search' => $search,
                'sort_column' => $sortBy,
                'sort_direction' => $sortDir,
            ],
        ]);
    }

    public function getNextOrdering()
    {
        return response()->json([
            'ordering' => nextOrdering(model: $this->productService->orderingQuery()),
        ]);
    }

    public function store(ProductRequest $request)
    {
        $this->productService->store(
            data: $request->safe()->except('image'),
            image: $request->file('image'),
        );

        return redirect()->back()->with('success', 'تم الإضافة بنجاح');
    }

    public function update(ProductRequest $request, Product $product)
    {
        $this->productService->update(
            product: $product,
            data: $request->safe()->except('image'),
            image: $request->file('image'),
        );

        return redirect()->back()->with('success', 'تم التحديث بنجاح');
    }

    public function destroy(Product $product)
    {
        $this->productService->delete(product: $product);

        return redirect()->back()->with('success', 'تم الحذف بنجاح');
    }
}
