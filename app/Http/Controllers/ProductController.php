<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProductListResources;
use App\Http\Resources\ProductShowResources;
use App\Models\Product;
use App\Models\ProductVariation;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     *
     */

    public function index(Request $request): Response
    {

        $keyword = $request->get('keyword');
        $products = Product::query()
            ->forWebsite()
            ->when($keyword, function ($query, $keyword) {
                $query->where(function ($query) use ($keyword) {
                    $query->where('name', 'like', '%' . $keyword . '%')
                        ->orWhere('description', 'like', '%' . $keyword . '%');
                });
            })
            ->paginate(20);

        return Inertia::render('Dashboard', [
            'products' => ProductListResources::collection($products),
        ]);

    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product, Request $request): Response
    {
        return Inertia::render('Product/Show', [
            'product'=> new ProductShowResources($product),
            'variation_type_options'=>$request->get('options', []),
        ]);
    }


}
