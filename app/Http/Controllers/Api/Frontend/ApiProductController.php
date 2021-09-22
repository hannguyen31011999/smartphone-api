<?php

namespace App\Http\Controllers\api\frontend;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Models\Product;
use App\Models\Slug;
use App\Models\Categories;
use App\Models\ProductVariant;
use Carbon\Carbon;

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
        $count = DB::table('product as pro')
                    ->rightJoin('categories as cate','cate.id','=','pro.categories_id')
                    ->rightJoin('product_variant as vari','vari.product_id','=','pro.id')
                    ->select([DB::raw('COUNT(vari.id) as products_count'),'cate.id'])
                    ->groupBy('cate.id')
                    ->get();
        $discount = $product->Discounts()->where('discount_end','>',Carbon::now())->first();
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
        $discount = $categories->Products()
                            ->with(['Discounts' => function($query){
                                $query->where('discount_end','>',Carbon::now());
                            }])
                            ->get();
        $count = DB::table('product as pro')
                ->rightJoin('categories as cate','cate.id','=','pro.categories_id')
                ->rightJoin('product_variant as vari','vari.product_id','=','pro.id')
                ->select([DB::raw('COUNT(vari.id) as products_count'),'cate.id'])
                ->groupBy('cate.id')
                ->get();
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
