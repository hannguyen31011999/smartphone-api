<?php

namespace App\Http\Controllers\api\frontend;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Models\Categories;
use App\Models\ProductVariant;

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
        return response()->json([
            'status_code'=>$this->codeSuccess,
            'data'=> [
                'categories'=>$result,
                'slugs'=>$slug
            ]
        ]);
    }

    public function getListProduct()
    {
        $result = ProductVariant::with(['Slugs','FirstProductSkus'])
                            ->orderBy('created_at','desc')
                            ->paginate(4);
        return response()->json([
            'status_code'=>$this->codeSuccess,
            'data'=>$result
        ]);
    }
}
