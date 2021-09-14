<?php

namespace App\Http\Controllers\api\frontend;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Builder;
use App\Models\ProductVariant;
use App\Models\Slug;
use App\Models\Product;

class ApiProductDetail extends Controller
{
    // http://localhost:8000/api/detail/iphone-xr-64gb
    public function index(Request $request,$url)
    {
        $arrID = [];
        try {
            $slug = Slug::where('slug_url','like','%'.$url.'%')->first();
            $product = ProductVariant::findOrFail($slug->product_variant_id);
            $sku = $product->ProductSkus()
                        ->orderBy('created_at')
                        ->get();
            $review = $product->Reviews()
                            ->orderBy('created_at')
                            ->paginate(3);
            $inventory = $product->InventoryManagements()
                            ->orderBy('created_at')
                            ->get();
            $products = Product::findOrFail($product->product_id);
            $discount = $products->Discounts()->first();
            $variant = $products->ProductVariants()->get();
            $categories = $products->Categories()->first();
            $option = $products->ProductOptions()->first();
            foreach($variant as $value){
                array_push($arrID,$value->id);
            }
            $slugs = Slug::whereIn('product_variant_id',$arrID)->get();
            return response()->json([
                'status_code' => $this->codeSuccess,
                'data' => [
                    'product'=>$product,
                    'product_sku'=>$sku,
                    'review'=>$review,
                    'inventory'=>$inventory,
                    'discount'=>$discount,
                    'variants'=>$variant,
                    'slug'=>$slugs,
                    'categories'=>$categories,
                    'option'=>$option
                ]
            ]);
        }catch(Exception $e){
            return response()->json([
                'status_code' => $this->codeFails,
                'message' => 'Server error not response'
            ],$this->codeFails);
        }
    }
}
