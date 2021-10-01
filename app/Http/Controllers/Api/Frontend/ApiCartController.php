<?php

namespace App\Http\Controllers\api\frontend;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\ProductSku;

class ApiCartController extends Controller
{
    // http://localhost:8000/api/cart/list?address_ip=127.0.0.1
    public function index(Request $request)
    {
        $result = [];
        try {
            if($request->query != null){
                $result = Cart::where('user_id','=',$request['query'])->get();
            }
            return response()->json([
                'status_code'=>$this->codeSuccess,
                'data'=>$result
            ]);
        }catch(Exception $e){
            return response()->json([
                'status_code'=>$this->codeFails,
                'message'=>'Serve not response data'
            ],$this->codeFails);
        }
    }

    // http://localhost:8000/api/cart/create?sku_id=63&name=Vivo V20 (2021)&unit_price=359&promotion_price=329&discount=0&color=Black&slug=vivo-v20-2021&image=123456.jpg&address_ip=127.0.0.1
    public function store(Request $request)
    {
        $cart = Cart::where('sku_id','=',$request->sku_id)->first();
        try {
            if($cart){
                $isBool = $cart->update([
                    'qty'=>$cart->qty + $request->qty
                ]);
                if($isBool){
                    return response()->json([
                        'status_code'=>$this->codeSuccess,
                        'data'=>$cart
                    ]);
                }
            }else {
                $input = $request->all();
                $result = Cart::create($input);
                if($result){
                    return response()->json([
                        'status_code'=>$this->codeSuccess,
                        'data'=>$result
                    ]);
                }
            }
        }catch(Exception $e){
            return response()->json([
                'status_code'=>$this->codeFails,
                'message'=>'Serve not response data'
            ],$this->codeFails);
        }
    }

    // http://localhost:8000/api/cart/update/1
    public function update(Request $request,$id)
    {
        try {
            $cart = Cart::findOrFail($id);
            $product = $cart->ProductSkus()->first();
            if((int)$product->sku_qty > ((int)$cart->qty + (int)$request->qty)){
                $isBool = $cart->update([
                    'qty'=>$cart->qty + $request->qty
                ]);
                if($isBool){
                    return response()->json([
                        'status_code'=>$this->codeSuccess,
                        'data'=>$cart
                    ]);
                }
            }else {
                return response()->json([
                    'status_code'=>$this->codeFails,
                    'message'=>'Sorry, Product is out of stock!'
                ]);
            }
        }catch(Exception $e){
            return response()->json([
                'status_code'=>$this->codeFails,
                'message'=>'Serve not response data'
            ],$this->codeFails);
        }
    }

    // http://localhost:8000/api/cart/delete/1
    public function destroy($id)
    {
        try {
            $cart = Cart::findOrFail($id);
            if($cart->delete()){
                return response()->json([
                    'status_code'=>$this->codeSuccess,
                    'message'=>'Delete cart success'
                ]);
            }
        }catch(Exception $e){
            return response()->json([
                'status_code'=>$this->codeFails,
                'message'=>'Serve not response data'
            ],$this->codeFails);
        }
    }
}
