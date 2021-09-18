<?php

namespace App\Http\Controllers\api\frontend;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Slug;
use App\Models\Categories;
use App\Models\ProductVariant;

class ApiProductController extends Controller
{
    // http://localhost:8000/api/product/iphone-12-pro-max
    public function index($slug)
    {
        $product = Slug::where('slug_url','like','%'.$slug.'%')->first()->Products()->first();
        $result = $product->ProductVariants()
                        ->with(['ProductSkus','Slugs','InventoryManagements'])
                        ->orderBy('created_at','desc')
                        ->paginate(6);
        $count = Categories::where('deleted_at',null)
                        ->withCount('Products')
                        ->orderBy('created_at','asc')
                        ->get(['id','categories_name']);
        $discount = $product->Discounts()->first();
        return response()->json([
            'status_code'=>$this->codeSuccess,
            'data'=>[
                'data'=>$result,
                'categories'=>$count,
                'discount'=>$discount
            ]
        ]);
    }

    // http://localhost:8000/api/categories/1
    public function getProductWithCategories($id)
    {
        $categories = Categories::findOrFail($id);
        $product = $categories->Products()->pluck('id');
        $result = ProductVariant::whereIn('product_id',$product)
                            ->with(['Slugs','ProductSkus'])
                            ->paginate(9);
        $discount = $categories->Products()->with('Discounts')->get();
        $count = Categories::where('deleted_at',null)
                        ->withCount('Products')
                        ->orderBy('created_at','asc')
                        ->get(['id','categories_name']);
        return response()->json([
            'status_code'=>$this->codeSuccess,
            'data'=>[
                'data'=>$result,
                'categories'=>$count,
                'discount'=>$discount
            ]
        ]);
    }
}
