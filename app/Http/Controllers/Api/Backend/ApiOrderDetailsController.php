<?php

namespace App\Http\Controllers\api\backend;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderDetails;

class ApiOrderDetailsController extends Controller
{
    // http://localhost:8000/api/admin/order/detail/164
    public function index($id)
    {
        try {
            $result = OrderDetails::where('order_id','=',$id)->orderBy('id')->get();
            return response()->json([
                'status_code'=>$this->codeSuccess,
                'data'=>$result
            ]);
        }catch(Exception $e){
            return response()->json([
                'status_code'=>$this->codeFails
            ]);
        }
    }
}
