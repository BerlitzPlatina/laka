<?php

namespace App\Http\Controllers;

use App\Jobs\ProductCreated;
use App\Models\Product;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ProductController extends Controller
{
    public function index(){
        $products = Product::all();
        return response($products, Response::HTTP_OK);
    }
    public function store(Request $request){
        $products = Product::create($request->only('product_name', 'product_stock'));
        ProductCreated::dispatch($products->toArray())->onQueue('default');
        return response($products, Response::HTTP_OK);
    }
}
