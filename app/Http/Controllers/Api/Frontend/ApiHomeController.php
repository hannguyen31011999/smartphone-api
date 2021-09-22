<?php

namespace App\Http\Controllers\api\frontend;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Models\Categories;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Visitor;
use Carbon\Carbon;

class ApiHomeController extends Controller
{
    // http://localhost:8000/api/categories
    public function getListCategories()
    {
        $result = Categories::with('Products')
                        ->where('deleted_at','=',null)
                        ->orderBy('created_at')
                        ->get();
        $slug = DB::table('product as pro')
                ->join('slug','pro.id','=','slug.product_id')
                ->select('slug.*')
                ->orderBy('slug.created_at')
                ->get();
        $discount = DB::table('discount')
                    ->leftJoin('product','discount.id','=','product.discount_id')
                    ->select('discount.*','product.id')
                    ->where('discount.discount_end','>=',Carbon::now())
                    ->orderBy('discount.created_at')
                    ->get();
        return response()->json([
            'status_code'=>$this->codeSuccess,
            'data'=> [
                'categories'=>$result,
                'slugs'=>$slug,
                'discounts'=>$discount
            ]
        ]);
    }

    // http://localhost:8000/api/product?page=2
    public function getListProduct()
    {
        $result = ProductVariant::with(['Slugs','FirstProductSkus','InventoryManagements'])
                            ->orderBy('created_at','desc')
                            ->paginate(4);
        return response()->json([
            'status_code'=>$this->codeSuccess,
            'data'=>$result
        ]);
    }

    // http://localhost:8000/api/product/promotion
    public function getProductDiscount()
    {
        $product = ProductVariant::with([
            'ProductSkus' => function($query) {
                $query->where('sku_promotion_price','>',0);
            },
            'Slugs'
        ])->orderBy('created_at','asc')->take(3)->get();
        $productDiscount = DB::table('product as pro')
                            ->rightJoin('discount as disc','pro.discount_id','=','disc.id')
                            ->rightJoin('product_variant as vari','pro.id','=','vari.product_id')
                            ->rightJoin('product_skus as sku','pro.id','=','sku.product_id')
                            ->rightJoin('slug','slug.product_variant_id','=','vari.id')
                            ->select('disc.*','vari.*','sku.*','slug.*')
                            ->where('pro.discount_id','!=',null)
                            ->where('disc.discount_end','>',Carbon::now())
                            ->orderBy('pro.created_at','asc')
                            ->take(1)
                            ->get();
        return response()->json([
            'status_code'=>$this->codeSuccess,
            'data'=>[
                'product'=>$product,
                'productDiscount'=>$productDiscount
            ]
        ]);
    }

    public function createVisitor(Request $request)
    {
        Visitor::create($request->all());
    }

    // http://localhost:8000/api/seach?keyword=iphone 12&categories=1
    public function seachProduct(Request $request)
    {
        if(!empty($request->keyword) && !empty($request->categories)){
            try {
                $categories = Categories::findOrFail($request->categories);
                $product = $categories->Products()
                                ->where('product_name','like','%'.$request->keyword)
                                ->first();
                if($product){
                    $slug = $product->Slugs()->first();
                    return response()->json([
                        'status_code'=>$this->codeSuccess,
                        'data'=>$slug
                    ]);
                }else {
                    return response()->json([
                        'status_code'=>$this->codeFails,
                        'message'=>'Product not found'
                    ]);
                }
            }catch(Exception $e){
                return response()->json([
                    'status_code'=>$this->codeFails,
                    'message'=>'Sorry, Please try again'
                ],$this->codeFails);
            }
        }else {
            try {
                $product =  Product::where('product_name','like','%'.$request->keyword)
                                ->first();
                if($product){
                    $slug = $product->Slugs()->first();
                    return response()->json([
                        'status_code'=>$this->codeSuccess,
                        'data'=>$slug
                    ]);
                }else {
                    return response()->json([
                        'status_code'=>$this->codeFails,
                        'message'=>'Product not found'
                    ]);
                }
            }catch(Exception $e){
                return response()->json([
                    'status_code'=>$this->codeFails,
                    'message'=>'Sorry, Please try again'
                ],$this->codeFails);
            }
        }
    }
}
