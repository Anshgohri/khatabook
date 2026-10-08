<?php

namespace App\Http\Controllers;

use App\Models\Product;
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
    public function shop()
    {
        $products = Product::latest()->get();
        return view('website.shop', compact('products'));
    }
    public function productDetail()
    {
        return view('website.shop-details');
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
