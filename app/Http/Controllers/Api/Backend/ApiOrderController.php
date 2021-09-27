<?php

namespace App\Http\Controllers\api\backend;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Models\Order;
use Carbon\Carbon;

class ApiOrderController extends Controller
{
    // http://localhost:8000/api/admin/order/list?pageSize=15
    public function index(Request $request)
    {
        try {
            $result = Order::where('deleted_at','=',null)
                        ->orderBy('created_at','desc')
                        ->paginate($request->pageSize);
            return response()->json([
                'status_code'=>$this->codeSuccess,
                'data'=>$result
            ]);
        }catch(Exception $e){
            return response()->json([
                'status_code'=>$this->codeFails,
            ],$this->codeFails);
        }
    }

    // http://localhost:8000/api/admin/order/update/164?order_status=3
    public function update(Request $request, $id)
    {
        try {
            $order = Order::findOrFail($id);
            if((int)$request->order_status == 3 && $order->order_status == 2 && $order->order_payment != 2){
                $detail = $order->OrderDetails()->get();
                foreach ($detail as $value) {
                    $sku = $value->ProductSkus()->where('sku_qty','>',$value->qty)->first();
                    $sku->update([
                        'sku_qty'=>(int)$sku->sku_qty - (int)$value->qty
                    ]);
                    $inventory = $sku->InventoryManagements()->where('qty','>',$value->qty)->first();
                    $inventory->update([
                        'qty'=>(int)$sku->sku_qty
                    ]);
                }
                $order->update($request->all());
            }else {
                $order->update($request->all());
            }
            return response()->json([
                'status_code'=>$this->codeSuccess,
                'data'=>$order
            ]);
        }catch(Exception $e){
            return response()->json([
                'status_code'=>$this->codeFails,
            ],$this->codeFails);
        }
    }

    // http://localhost:8000/api/admin/order/seach?keyword=taikhoan&pageSize=15
    public function seach(Request $request)
    {
        try {
            $result = null;
            if(!empty($request->keyword)){
                $result = Order::where('id',$request->keyword)
                                ->orWhere('user_id','=',$request->keyword)
                                ->orWhere('order_email','like',$request->keyword.'%')
                                ->orWhere('order_name','like',$request->keyword.'%')
                                ->orWhere('order_phone','like',$request->keyword.'%')
                                ->orWhere('payment_option','like',$request->keyword.'%')
                                ->orderBy('created_at','desc')
                                ->paginate($request->pageSize);
            }else {
                $result = Order::orderBy('created_at','desc')->paginate($request->pageSize);
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

    // http://localhost:8000/api/admin/order/export?month=9
    public function exportOrder(Request $request)
    {
        try {
            $result = Order::with('OrderDetails')
                        ->whereYear('created_at','=',Carbon::now()->year)
                        ->whereMonth('created_at','=',$request->month)
                        ->where('deleted_at','=',null)
                        ->orderBy('created_at','desc')
                        ->get();
            return response()->json([
                'status_code'=>$this->codeSuccess,
                'data'=>$result
            ]);
        }catch(Exception $e){
            return response()->json([
                'status_code'=>$this->codeFails,
            ],$this->codeFails);
        }
    }
}
