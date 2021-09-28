<?php

namespace App\Http\Controllers\api\backend;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Models\Order;
use App\Models\OrderDetails;
use App\Models\Categories;
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
            $revenue = DB::table('order')
                        ->rightJoin('order_details as ord','ord.order_id','=','order.id')
                        ->select(
                            DB::raw('sum(ord.product_price * ord.qty) as total'),
                            DB::raw('MONTH(ord.created_at) as month')
                        )->whereYear('ord.created_at','=',Carbon::now()->year)
                        ->whereMonth('ord.created_at','=',Carbon::now()->month)
                        ->where('order.order_status',3)
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

    // http://localhost:8000/api/admin/dashboard/chart
    public function getDataWithCategory()
    {
        try {
            $categories = Categories::where('deleted_at',null)
                            ->with(['Products' => function($query) {
                                $query->withCount('ProductVariants');
                            }])->orderBy('id')->get();
            $user = DB::table('user')
                    ->select([
                        DB::raw('count(id) as total'),
                        DB::raw('MONTH(created_at) as month')
                    ])
                    ->whereYear('created_at',Carbon::now()->year)
                    ->where('role','!=',2)
                    ->orderBy('id')
                    ->groupBy('month')
                    ->get();
            $order = Order::select([
                            DB::raw('count(id) as total'),
                            DB::raw('MONTH(created_at) as month')
                        ])->whereYear('created_at',Carbon::now()->year)
                        ->orderBy('id')
                        ->groupBy('month')
                        ->get();

            return response()->json([
                'status_code'=>$this->codeSuccess,
                'data'=>[
                    'categories'=>$categories,
                    'user'=>$user,
                    'order'=>$order
                ]
            ]);
        }catch(Exception $e){
            return response()->json([
                'status_code'=>$this->codeFails
            ],$this->codeFails);
        }        
    }
}
