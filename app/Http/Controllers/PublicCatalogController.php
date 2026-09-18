<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicCatalogController extends Controller
{
    /**
     * Display landing page with admin categories and 4 to 6 featured products.
     */
    public function index(Request $request): View
    {
        $categories = ProductCategory::withCount('products')->orderBy('name')->get();

        $query = Product::query()->with('category');

        if ($request->filled('category_id')) {
            $query->where('product_category_id', $request->input('category_id'));
        }

        if ($request->filled('q')) {
            $search = $request->input('q');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Display top 6 featured products on landing page
        $featuredProducts = $query->latest()->take(6)->get();

        $totalProductsCount = Product::count();

        return view('welcome', compact('categories', 'featuredProducts', 'totalProductsCount'));
    }

    /**
     * Display full catalog listing of all admin products with search & category filter.
     */
    public function catalog(Request $request): View
    {
        $categories = ProductCategory::withCount('products')->orderBy('name')->get();

        $query = Product::query()->with('category');

        if ($request->filled('category_id')) {
            $query->where('product_category_id', $request->input('category_id'));
        }

        if ($request->filled('q')) {
            $search = $request->input('q');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        $products = $query->latest()->paginate(12)->withQueryString();
        $selectedCategory = $request->filled('category_id') ? ProductCategory::find($request->input('category_id')) : null;

        return view('catalog.index', compact('categories', 'products', 'selectedCategory'));
    }

    /**
     * Display product detail page with description, specs, and WhatsApp inquiry options.
     */
    public function show(Product $product): View
    {
        $product->load('category');

        $relatedProducts = Product::query()
            ->with('category')
            ->where('id', '!=', $product->id)
            ->when($product->product_category_id, function ($q) use ($product) {
                $q->where('product_category_id', $product->product_category_id);
            })
            ->latest()
            ->take(4)
            ->get();

        // If not enough in same category, fill with latest products
        if ($relatedProducts->count() < 4) {
            $additional = Product::query()
                ->with('category')
                ->where('id', '!=', $product->id)
                ->whereNotIn('id', $relatedProducts->pluck('id'))
                ->latest()
                ->take(4 - $relatedProducts->count())
                ->get();
            $relatedProducts = $relatedProducts->concat($additional);
        }

        return view('catalog.show', compact('product', 'relatedProducts'));
    }
}
