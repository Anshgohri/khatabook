<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Providers\AppServiceProvider;
use Illuminate\Http\Request;

class WebsiteController extends Controller
{
    public function index()
    {
        $products = Product::latest()->take(10)->get();
        return view('website.index', compact('products'));
    }
    public function login()
    {
        return view('website.login');
    }
    public function about()
    {
        return view('website.about');
    }
    public function contact()
    {
        return view('website.contact');
    }
    public function shop(Request $request)
    {
        $categories = ProductCategory::query()->orderBy('name')->get();

        $query = Product::query()->with('category');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $like = AppServiceProvider::likeOperator();
            $query->where(function ($sub) use ($search, $like) {
                $sub->where('name', $like, "%{$search}%")
                    ->orWhere('description', $like, "%{$search}%");
            });
        }

        if ($request->filled('category')) {
            $catId = $request->input('category');
            if ($catId === 'uncategorized') {
                $query->whereNull('product_category_id');
            } else {
                $query->where('product_category_id', $catId);
            }
        }

        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        switch ($request->input('sort')) {
            case 'price_low_high':
                $query->orderBy('unit_price', 'asc');
                break;
            case 'price_high_low':
                $query->orderBy('unit_price', 'desc');
                break;
            case 'latest':
            default:
                $query->latest();
                break;
        }

        $products = $query->get();

        return view('website.shop', compact('products', 'categories'));
    }
    public function productDetail()
    {
        $product = Product::first();
        if ($product) {
            return redirect()->route('catalog.show', $product->id);
        }
        return redirect()->route('shop');
    }
    public function cart()
    {
        return view('website.shop-cart');
    }
    public function wishlist()
    {
        return view('website.shop-wishlist');
    }
    public function checkout()
    {
        return view('website.shop-checkout');
    }
    public function myAccount()
    {
        return view('website.my-account');
    }
}
