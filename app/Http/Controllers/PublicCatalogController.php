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
    public function index(Request $request)
    {
        return redirect()->route('home');
    }

    /**
     * Display full catalog listing of all admin products with search & category filter.
     */
    public function catalog(Request $request)
    {
        return redirect()->route('shop');
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

        return view('website.shop-details', compact('product', 'relatedProducts'));
    }
}
