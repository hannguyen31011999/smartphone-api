<?php

namespace App\Http\Controllers\api\backend;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\InventoryManagement;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class ApiInventoryController extends Controller
{
    public function index(Request $request)
    {
        $result = InventoryManagement::with(['ProductVariants','ProductSkus'])
                                    ->where('deleted_at','=',null)
                                    ->orderBy('id')
                                    ->paginate($request->pageSize);
        return response()->json([
            'status_code' => $this->codeSuccess,
            'data' => $result
        ]);
    }

    public function getListProduct(Request $request)
    {
        $result = ProductVariant::with('ProductSkus')
                                    ->where('deleted_at','=',null)
                                    ->orderBy('id')
                                    ->get();
        return response()->json([
            'status_code' => $this->codeSuccess,
            'data' => $result
        ]);
    }

    // http://localhost:8000/api/admin/inventory/create?product_id=54&variant_id=56&sku_id=36&unit_price=3&promotion_price=3&qty=3
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(),
            [
                'unit_price'=>'required|numeric|max:9999',
                'qty'=>'required|numeric|max:100',
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
            $variant = ProductVariant::findOrFail($request->variant_id);
            $input['status'] = 0;
            $input['product_id'] = $variant->product_id;
            $inventory = InventoryManagement::create($input);
            $isSku = $inventory->ProductSkus()->update([
                'sku_unit_price'=>$request->unit_price,
                'sku_promotion_price'=>$request->promotion_price ? $request->promotion_price : null,
                'sku_qty'=>$request->qty
            ]);
            if(!$inventory == null && $isSku){
                return response()->json([
                    'status_code' => $this->codeSuccess,
                    'data' => $inventory->with(['ProductVariants','ProductSkus'])
                                        ->latest()
                                        ->first()
                ]);
            }
        }catch(Exception $e){
            return response()->json([
                'status_code' => $this->codeFails,
                'message' => 'Server error not response'
            ],$this->codeFails);
        }
    }

    public function edit($id)
    {

    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(),
            [
                'unit_price'=>'required|numeric|max:9999',
                'qty'=>'required|numeric|max:100',
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
            $input['status'] = 2;
            $inventory = InventoryManagement::findOrFail($id);
            $isInventory = $inventory->update($input);
            $isSku = $inventory->ProductSkus()->update([
                'sku_unit_price'=>$request->unit_price,
                'sku_promotion_price'=>$request->promotion_price ? $request->promotion_price : null,
                'sku_qty'=>$request->qty
            ]);
            if($isInventory && $isSku){
                return response()->json([
                    'status_code' => $this->codeSuccess,
                    'data' => $inventory->with(['ProductVariants','ProductSkus'])
                                        ->latest()
                                        ->first()
                ]);
            }
        }catch(Exception $e){
            return response()->json([
                'status_code' => $this->codeFails,
                'message' => 'Server error not response'
            ],$this->codeFails);
        }
    }

    public function updateStatus(Request $request, $id)
    {
        try {
            $inventory = InventoryManagement::findOrFail($id);
            $isInventory = $inventory->update([
                'status'=>$request->status
            ]);
            if($isInventory){
                return response()->json([
                    'status_code' => $this->codeSuccess,
                ]);
            }
        }catch(Exception $e){
            return response()->json([
                'status_code' => $this->codeFails,
                'message' => 'Server error not response'
            ],$this->codeFails);
        }
    }

    // http://localhost:8000/api/admin/inventory/seach?keyword=iphone 12&pageSize=15
    public function seach(Request $request)
    {
        try {
            $result = null;
            if(!empty($request->keyword)){
                $result = DB::table('inventory_management as inve')
                                    ->leftJoin('product as pro','inve.product_id','=','pro.id')
                                    ->leftJoin('product_variant as variant','inve.variant_id','=','variant.id')
                                    ->leftJoin('product_skus as sku','inve.sku_id','=','sku.id')
                                    ->select('inve.*','variant.*','sku.*')
                                    ->where('inve.id','=',(int)$request->keyword)
                                    ->orWhere(function($query) use($request){
                                        $query->where('pro.product_name','like',$request->keyword.'%')
                                            ->orWhere('variant.product_variant_name','like',$request->keyword.'%')
                                            ->orWhere('sku.color','like',$request->keyword.'%');
                                    })
                                    ->orWhere(function($query) use($request){
                                        $query->whereBetween('sku.sku_unit_price',[0,$request->keyword]);
                                    })
                                    ->orderBy('inve.created_at','desc')
                                    ->paginate($request->pageSize);
            }else {
                $result = InventoryManagement::with(['ProductVariants','ProductSkus'])
                                    ->where('deleted_at','=',null)
                                    ->orderBy('id')
                                    ->paginate($request->pageSize);
            }
            return response()->json([
                'status_code' => $this->codeSuccess,
                'data' => $result
            ]);
        }catch(Exception $e){
            return response()->json([
                'status_code' => $this->codeFails,
                'message' => 'Server error not response'
            ],$this->codeFails);
        }
    }
}
