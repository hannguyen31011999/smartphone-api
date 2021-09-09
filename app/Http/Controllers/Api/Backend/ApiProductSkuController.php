<?php

namespace App\Http\Controllers\api\backend;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\ProductVariant;
use App\Models\ProductSku;
use App\Models\InventoryManagement;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Image;

class ApiProductSkuController extends Controller
{
    public function index(Request $request,$id)
    {
        $variant = ProductVariant::findOrFail($id);
        $result = $variant->ProductSkus()->paginate($request->pageSize);
        return response()->json([
            'status_code' => $this->codeSuccess,
            'data' => $result
        ]);
    }

    public function store(Request $request,$id)
    {
        $variant = ProductVariant::findOrFail($id);
        $validator = Validator::make($request->all(),
            [
                'image'=>'required',
                'sku_unit_price'=>'required|numeric|max:9999',
                'sku_qty'=>'required|numeric|max:100',
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
            $input = $request->all();
            $input['product_id'] = $variant->product_id;
            if($request->hasFile('image')){
                $file = $request->file('image');
                $fileName = time() . '.' . $file->getClientOriginalExtension();
                $image = Image::make($file);
                $image->resize(215,215, function ($constraint) {
                    $constraint->aspectRatio();
                })->encode($file->getClientOriginalExtension());
                Storage::disk('product')->put($fileName,(string)$image);
                $input['sku_image'] = $fileName;
                $result = $variant->ProductSkus()->create($input);
                $inventory = $variant->InventoryManagements()->create([
                    'product_id'=>$variant->product_id,
                    'sku_id'=>$result->id,
                    'unit_price'=>$request->sku_unit_price,
                    'promotion_price' => $request->sku_promotion_price != 'undefined' ? $request->sku_promotion_price : null,
                    'qty'=> $request->sku_qty,
                    'status'=>0
                ]);
                if(!$result == null && !$inventory == null){
                    return response()->json([
                        'status_code' => $this->codeSuccess,
                        'data' => $result
                    ]);
                }
            }
        }catch(Exception $e){
            return response()->json([
                'status_code' => $this->codeFails,
                'message' => 'Server error not response'
            ],$this->codeFails);
        }
    }

    public function update(Request $request, $id)
    {
        $image = null;
        $fileName = null;
        $sku = ProductSku::findOrFail($id);
        $input = $request->all();
        $validator = Validator::make($input,
            [
                'sku_unit_price'=>'numeric|max:9999',
                'color'=>'max:50'
            ]
        );
        if($validator->fails()){
            return response()->json([
                'status_code' => 422,
                'message' => $validator->errors()
            ],200);
        }
        try {
            if($request->hasFile('image')){
                if (Storage::disk('product')->exists($sku->sku_image)) {
                    Storage::disk('product')->delete($sku->sku_image);
                }
                $file = $request->file('image');
                $fileName = time() . '.' . $file->getClientOriginalExtension();
                $image = Image::make($file);
                $image->resize(215,215, function ($constraint) {
                    $constraint->aspectRatio();
                })->encode($file->getClientOriginalExtension());
                Storage::disk('product')->put($fileName,(string)$image);
                $input['sku_image'] = $fileName;
            }
            $isBool = $sku->update($input);
            if($isBool){
                return response()->json([
                    'status_code' => $this->codeSuccess,
                    'data' => $sku
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
        try {
            $sku = ProductSku::findOrFail($id);
            if($sku->delete()){
                if (Storage::disk('product')->exists($sku->sku_image)) {
                    Storage::disk('product')->delete($sku->sku_image);
                }
                return response()->json([
                    'status_code' => $this->codeSuccess,
                    'message' => 'delete post success'
                ]);
            }
        }catch(Exception $e){
            return response()->json([
                'status_code' => $this->codeFails,
                'message' => 'Server error not response'
            ],$this->codeFails);
        }
    }

    public function seach(Request $request)
    {

    }
}
