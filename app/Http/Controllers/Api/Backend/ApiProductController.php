<?php

namespace App\Http\Controllers\api\backend;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\InventoryManagement;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Image;

class ApiProductController extends Controller
{
    // http://localhost:8000/api/admin/product/list?pageSize=15
    public function index(Request $request)
    {
        $categories = [];
        $discount = [];
        $result = Product::where('deleted_at','=',null)
                        ->with(['ProductOptions','ProductVariants','ProductSkus'])
                        ->orderBy('id')
                        ->paginate($request->pageSize);
        if(!$request->page){
            $categories = DB::table('categories')
                        ->where('deleted_at','=',null)
                        ->orderBy('id')
                        ->get();
            $discount = DB::table('discount')
                        ->where('deleted_at','=',null)
                        ->orderBy('id')
                        ->get();
        }
        return response()->json([
            'status_code' => $this->codeSuccess,
            'data' => $result,
            'parent' => [
                'categories'=>$categories,
                'discount'=>$discount
            ]
        ]);
    }

    // http://localhost:8000/api/admin/product/parent
    public function getParentProduct(){
        
        return response()->json([
            'status_code' => $this->codeSuccess,
            'data' => [
                'categories'=>$categories,
                'discount'=>$discount
            ]
        ]);
    }

    // http://localhost:8000/api/admin/product/create?categories_id=1&discount_id=&product_name=Điện thoại Iphone Xsmax&product_desc=Điện thoại sang, xịn ,mịn
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(),
            [
                'categories_id'=>'required',
                'product_name'=>'required|max:254|unique:product,product_name',
                'product_desc'=>'max:3000',
                'image'=>'required',
                'screen'=>'max:254',
                'screen_resolution'=>'max:254',
                'operating_system'=>'max:100',
                'cpu'=>'max:100',
                'gpu'=>'max:100',
                'ram'=>'max:100|numeric',
                'camera_fr'=>'max:255',
                'camera_ba'=>'max:255',
                'pin'=>'max:254',
                'product_variant_name'=>'max:254|unique:product_variant,product_variant_name',
                'product_variant_rom'=>'required|numeric|max:99',
                'sku_unit_price'=>'required|numeric|max:9999',
                'sku_qty'=>'required|max:100|numeric',
                'color'=>'required|max:50'
            ]
        );
        if($validator->fails()){
            return response()->json([
                'status_code' => 422,
                'message' => $validator->errors()
            ],200);
        }
        try {
            $product = Product::create([
                'categories_id'=>$request->categories_id,
                'discount_id'=>$request->discount_id ? $request->discount_id : null,
                'product_name'=>$request->product_name,
                'product_desc'=>$request->product_desc ? $request->product_desc : null
            ]);
            $product->Slugs()->create([
                'slug_url'=>utf8tourl($request->product_name)
            ]);
            $product->ProductOptions()->create([
                'screen'=>$request->screen ? $request->screen : null,
                'screen_resolution'=>$request->screen_resolution ? $request->screen_resolution : null,
                'operating_system'=>$request->operating_system ? $request->operating_system : null,
                'cpu'=>$request->cpu ? $request->cpu : null,
                'gpu'=>$request->gpu ? $request->gpu : null,
                'ram'=>$request->ram ? $request->ram : null,
                'camera_fr'=>$request->camera_fr ? $request->camera_fr : null,
                'camera_ba'=>$request->camera_ba ? $request->camera_ba : null,
                'pin'=>$request->pin ? $request->pin : null,
            ]);
            $variant = $product->ProductVariants()->create([
                'product_variant_name' => $request->product_variant_name ? $request->product_variant_name : null,
                'product_variant_rom' => $request->product_variant_rom
            ]);
            $variant->Slugs()->create([
                'slug_url'=>utf8tourl($request->product_variant_name)
            ]);
            $sku = null;
            if($request->hasFile('image')){
                $file = $request->file('image');
                $fileName = time() . '.' . $file->getClientOriginalExtension();
                $image = Image::make($file);
                $image->resize(215,215, function ($constraint) {
                    $constraint->aspectRatio();
                })->encode($file->getClientOriginalExtension());
                Storage::disk('product')->put($fileName,(string)$image);
                $sku = $variant->ProductSkus()->create([
                    'product_id'=>$product->id,
                    'sku_unit_price'=>$request->sku_unit_price,
                    'sku_promotion_price'=>$request->sku_promotion_price ? $request->sku_promotion_price : null,
                    'sku_qty'=>$request->sku_qty,
                    'color'=>$request->color,
                    'sku_image'=>$fileName
                ]);
            }
            $inventory = InventoryManagement::create([
                'product_id'=>$product->id,
                'variant_id'=>$variant->id,
                'sku_id'=>$sku->id,
                'unit_price'=>$request->sku_unit_price,
                'promotion_price'=>$request->sku_promotion_price ? $request->sku_promotion_price : null,
                'qty'=>$request->sku_qty,
                'status'=>0
            ]);
            if($product && $variant && $sku && $inventory){
                return response()->json([
                    'status_code' => $this->codeSuccess,
                    'data' => $product->with(['ProductOptions','ProductVariants','ProductSkus'])->latest()->first()
                ]);
            }
        }catch(Exception $e){
            return response()->json([
                'status_code' => $this->codeFails,
                'message' => 'Server error not response'
            ],$this->codeFails);
        }
    }

    // http://localhost:8000/api/admin/product/edit/1
    public function edit($id)
    {
        try {
            $product = Product::findOrFail($id);
            if(!$product == null){
                return response()->json([
                    'status_code' => $this->codeSuccess,
                    'data' => $product
                ]);
            }
        }catch(Exception $e){
            return response()->json([
                'status_code' => $this->codeFails,
                'message' => 'Server error not response'
            ],$this->codeFails);
        }
    }

    // 
    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        if(strcmp($product->product_name,$request->product_name)!=0){
            $validator = Validator::make($request->all(),
                [
                    'categories_id'=>'required',
                    'product_name'=>'required|max:254|unique:product,product_name',
                    'product_desc'=>'max:3000',
                    'screen'=>'max:254',
                    'screen_resolution'=>'max:254',
                    'operating_system'=>'max:100',
                    'cpu'=>'max:100',
                    'gpu'=>'max:100',
                    'ram'=>'max:10|numeric',
                    'camera_fr'=>'max:255',
                    'camera_ba'=>'max:255',
                    'pin'=>'max:254'
                ]
            );
            if($validator->fails()){
                return response()->json([
                    'status_code' => 422,
                    'message' => $validator->errors()
                ],200);
            }
        }
        try {
            $isProduct = $product->update([
                'categories_id'=>$request->categories_id ? $request->categories_id : $product->categories_id,
                'discount_id'=>$request->discount_id ? $request->discount_id : $product->discount_id,
                'product_name'=>$request->product_name ? $request->product_name : $product->product_name,
                'product_desc'=>$request->product_desc ? $request->product_desc : $product->product_desc
            ]);
            $isSlug = $product->Slugs()->update([
                'slug_url'=>utf8tourl($request->product_name)
            ]);
            $options = $product->ProductOptions()->first();
            $isOption = $options->update([
                'screen'=>$request->screen ? $request->screen : $options->screen,
                'screen_resolution'=>$request->screen_resolution ? $request->screen_resolution : $options->screen_resolution,
                'operating_system'=>$request->operating_system ? $request->operating_system : $options->operating_system,
                'cpu'=>$request->cpu ? $request->cpu : $options->cpu,
                'gpu'=>$request->gpu ? $request->gpu : $options->gpu,
                'ram'=>$request->ram ? $request->ram : $options->ram,
                'camera'=>$request->camera ? $request->camera : $options->camera,
                'pin'=>$request->pin ? $request->pin : $options->pin,
            ]);
            $result = $product->with(['ProductOptions','ProductVariants','ProductSkus'])
                            ->whereHas('ProductOptions',function( $q ) use ( $product ){
                                $q->where('product_id','=',$product->id );
                            })
                            ->whereHas('ProductVariants',function( $q ) use ( $product ){
                                $q->where('product_id','=',$product->id );
                            })
                            ->whereHas('ProductSkus',function( $q ) use ( $product ){
                                $q->where('product_id','=',$product->id );
                            })
                            ->first();
            if($isProduct && $isOption && $isSlug){
                return response()->json([
                    'status_code' => $this->codeSuccess,
                    'data' => $result
                ]);
            }
        }catch(Exception $e){
            return response()->json([
                'status_code' => $this->codeFails,
                'message' => 'Server error not response'
            ],$this->codeFails);
        }
    }

    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        $isVariant = $product->ProductVariants()->delete();
        $isOption = $product->ProductOptions()->delete();
        $isSku = $product->ProductSkus()->delete();
        $isProduct = $product->delete();
        try {
            if($isOption && $isProduct && $isSku && $isVariant){
                return response()->json([
                    'status_code' => $this->codeSuccess,
                    'message' => 'delete product success'
                ]);
            }
        }catch(Exception $e){
            return response()->json([
                'status_code' => $this->codeFails,
                'message' => 'Server error not response',
                'data' => $categories
            ],$this->codeFails);
        }
    }

    public function seach(Request $request)
    {
        try {
            $result = [];
            if($request->keyword!=null){
                $product = Product::where('deleted_at','=',null)
                                ->where('id','=',(int)$request->keyword)
                                ->first();
                $result = Product::with(['ProductOptions','ProductVariants','ProductSkus'])
                                ->whereHas('ProductOptions',function( $q ) use ( $product ){
                                    $q->where('product_id','=',$product->id );
                                })
                                ->whereHas('ProductVariants',function( $q ) use ( $product ){
                                    $q->where('product_id','=',$product->id );
                                })
                                ->whereHas('ProductSkus',function( $q ) use ( $product ){
                                    $q->where('product_id','=',$product->id );
                                })
                                ->orderBy('id')
                                ->paginate($request->pageSize);
            }else {
                $result = Product::where('deleted_at','=',null)
                                ->with(['ProductOptions','ProductVariants','ProductSkus'])
                                ->orderBy('id')
                                ->paginate($request->pageSize);
            }
            $categories = DB::table('categories')
                                ->where('deleted_at','=',null)
                                ->orderBy('id')
                                ->get();
            $discount = DB::table('discount')
                            ->where('deleted_at','=',null)
                            ->orderBy('id')
                            ->get();
            return response()->json([
                'status_code' => $this->codeSuccess,
                'data' => $result,
                'parent' => [
                    'categories'=>$categories,
                    'discount'=>$discount
                ]
            ]);
        }catch(Exception $e){
            return response()->json([
                'status_code' => $this->codeFails,
                'message' => 'Server error not response'
            ],$this->codeFails);
        }
    }
    
    public function createVariant(Request $request,$id){
        $validator = Validator::make($request->all(),
            [
                'image'=>'required',
                'product_variant_name'=>'required|max:254|unique:product_variant,product_variant_name',
                'product_variant_rom'=>'required|numeric',
                'sku_unit_price'=>'required|numeric',
                'sku_qty'=>'required|numeric',
                'color'=>'required|max:50'
            ]
        );
        if($validator->fails()){
            return response()->json([
                'status_code' => 422,
                'message' => $validator->errors()
            ],200);
        }
        try {
            $product = Product::findOrFail($id);
            $variant = $product->ProductVariants()->create([
                'product_variant_name' => $request->product_variant_name,
                'product_variant_rom' => $request->product_variant_rom,
            ]);
            $variant->Slugs()->create([
                'slug_url'=>utf8tourl($request->product_variant_name)
            ]);
            $sku = null;
            if($request->hasFile('image')){
                $file = $request->file('image');
                $fileName = time() . '.' . $file->getClientOriginalExtension();
                $image = Image::make($file);
                $image->resize(270,320, function ($constraint) {
                    $constraint->aspectRatio();
                })->encode($file->getClientOriginalExtension());
                Storage::disk('product')->put($fileName,(string)$image);
                $sku = $variant->ProductSkus()->create([
                    'product_id'=>$product->id,
                    'sku_unit_price'=>$request->sku_unit_price,
                    'sku_promotion_price'=>$request->sku_promotion_price ? $request->sku_promotion_price : null,
                    'sku_qty'=>$request->sku_qty,
                    'color'=>$request->color,
                    'sku_image'=>$fileName
                ]);
            }
            $inventory = InventoryManagement::create([
                'product_id'=>$product->id,
                'variant_id'=>$variant->id,
                'sku_id'=>$sku->id,
                'unit_price'=>$request->sku_unit_price,
                'promotion_price'=>$request->sku_promotion_price ? $request->sku_promotion_price : null,
                'qty'=>$request->sku_qty,
                'status'=>0
            ]);
            if($inventory && $sku && $product && $variant){
                return response()->json([
                    'status_code' => $this->codeSuccess,
                    'data' => [
                        'variant'=>$variant,
                        'sku'=>$sku
                    ]
                ]);
            }
        }catch(Exception $e){
            return response()->json([
                'status_code' => $this->codeFails,
                'message' => 'Server error not response',
                'data' => $categories
            ],$this->codeFails);
        }
    }

    public function updateVariant(Request $request,$id){
        $variant = ProductVariant::findOrFail($id);
        if(strcmp($request->product_variant_name,$variant->product_variant_name)){
            $validator = Validator::make($request->all(),
                [
                    'product_variant_name'=>'required|max:254|unique:product_variant,product_variant_name',
                    'product_variant_rom'=>'required|numeric'
                ]
            );
            if($validator->fails()){
                return response()->json([
                    'status_code' => 422,
                    'message' => $validator->errors()
                ],200);
            }
        }
        try{
            $isVariant = $variant->update($request->all());
            $isSlug = $variant->Slugs()->update([
                'slug_url'=>utf8tourl($request->product_variant_name)
            ]);
            if($isVariant && $isSlug){
                return response()->json([
                    'status_code' => $this->codeSuccess,
                    'data' => $variant
                ]);
            }
        }catch(Exception $e){
            return response()->json([
                'status_code' => $this->codeFails,
                'message' => 'Server error not response',
                'data' => $categories
            ],$this->codeFails);
        }
    }

    public function deleteVariant($id)
    {
        try {
            $variant = ProductVariant::findOrFail($id);
            $isVariant = $variant->delete();
            $isSku = false;
            $sku = $variant->ProductSkus()->get();
            foreach ($sku as $value) {
                if (Storage::disk('product')->exists($value->sku_image)) {
                    Storage::disk('product')->delete($value->sku_image);
                    $isSku = $value->delete();
                }
            }
            if($isVariant && $isSku){
                return response()->json([
                    'status_code' => $this->codeSuccess,
                    'message' => 'delete product variant success'
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
