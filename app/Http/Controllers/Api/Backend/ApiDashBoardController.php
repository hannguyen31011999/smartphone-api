<?php

namespace App\Http\Controllers\api\backend;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Models\Order;
use App\Models\OrderDetails;
use App\Models\User;
use App\Models\Visitor;
use Carbon\Carbon;

class ApiDashBoardController extends Controller
{
    // http://localhost:8000/api/admin/dashboard/count
    public function countGroup()
    {
        try {
            $order = Order::whereYear('created_at','=',Carbon::now()->year)
                    ->whereMonth('created_at','=',Carbon::now()->month)
                    ->count();
            $revenue = OrderDetails::select(
                                DB::raw('sum(product_price * qty) as total'),
                                DB::raw('MONTH(created_at) as month'))
                                ->whereYear('created_at','=',Carbon::now()->year)
                                ->whereMonth('created_at','=',Carbon::now()->month)
                                ->groupBy('month')
                                ->get();
            $user = User::where('role','!=',2)->count();
            $visitor = Visitor::count();
            return response()->json([
                'status_code'=>$this->codeSuccess,
                'data'=>[
                    'order'=>$order,
                    'revenue'=>$revenue,
                    'user'=>$user,
                    'visitor'=>$visitor
                ]
            ]);
        }catch(Exception $e){
            return response()->json([
                'status_code'=>$this->codeFails
            ],$this->codeFails);
        }
    }
}
