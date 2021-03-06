<?php

namespace App\Http\Controllers\api\frontend;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Validator;
use App\Models\ProductVariant;
use App\Models\Slug;
use App\Models\Product;
use App\Models\Review;
use Carbon\Carbon;

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
                        ->with(['InventoryManagements'=>function($query){
                            return $query->where('qty','>',0)->get();
                        }])
                        ->orderBy('created_at')
                        ->get();
            $review = $product->Reviews()
                            ->where('review_status','!=',2)
                            ->orderBy('created_at')
                            ->paginate(3);
            $products = Product::findOrFail($product->product_id);
            $discount = $products->Discounts()->where('discount_end','>',Carbon::now())->first();
            $variant = $products->ProductVariants()->with('Slugs')->get();
            $categories = $products->Categories()->first();
            $option = $products->ProductOptions()->first();
            return response()->json([
                'status_code' => $this->codeSuccess,
                'data' => [
                    'product'=>$product,
                    'product_sku'=>$sku,
                    'review'=>$review,
                    'discount'=>$discount,
                    'variants'=>$variant,
                    'categories'=>$categories,
                    'option'=>$option,
                ]
            ]);
        }catch(Exception $e){
            return response()->json([
                'status_code' => $this->codeFails,
                'message' => 'Server error not response'
            ],$this->codeFails);
        }
    }

    // http://localhost:8000/api/detail/review/create?product_variant_id=20&review_name=Nguy???n V??n H??ng&review_email=hungnguyen311999@gmai.com&review_star=4&review_content=S???n ph???m r???t t???t&review_phone=0382484247
    public function createReview(Request $request)
    {
        $validator = Validator::make($request->all(),
            [
                'review_name'=>'required|max:100',
                'review_email'=>'required|max:100',
                'review_star'=>'required',
                'review_content'=>'required|max:254'
            ]
        );
        if($validator->fails()){
            return response()->json([
                'status_code' => 422,
                'message' => $validator->errors()
            ],200);
        }
        try {
            $input = $request->all();
            $input['review_status'] = 1;
            $review = Review::create($input);
            if(!$review == null){
                return response()->json([
                    'status_code' => $this->codeSuccess,
                    'data' => $review
                ]);
            }
        }catch(Exception $e){
            return response()->json([
                'status_code' => $this->codeFails,
                'message' => 'Server error not response'
            ],$this->codeFails);
        }
    }
}
