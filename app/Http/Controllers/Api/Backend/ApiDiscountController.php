<?php

namespace App\Http\Controllers\api\backend;

use App\Models\Discount;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;
class ApiDiscountController extends Controller
{

    // http://localhost:8000/api/admin/discount/list
    public function index(Request $request)
    {
        $result = DB::table('discount')
                    ->where('deleted_at','=',null)
                    ->orderBy('id')
                    ->paginate($request->pageSize);
        return response()->json([
            'status_code' => $this->codeSuccess,
            'data' => $result
        ]);
    }

    // http://localhost:8000/api/admin/discount/create?discount_name=abababa&discount_type=Trade discount&discount_value=999999&discount_end=2021-08-20 23:59:00
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(),
            [
                'discount_name'=>'required|max:254|unique:discount,discount_name',
                'discount_type'=>'required',
                'discount_value'=>'required|max:10',
                'discount_start'=>'required',
                'discount_end'=>'required'
            ],
            [
                'discount_name.required'=>'Discount is empty',
                'discount_name.max'=>'Discount is maximum 254 character',
                'discount_name.unique'=>'Discount is exist',
                'discount_type.required'=>'Discount type is not valid',
                'discount_value.required'=>'Discount values is not valid',
                'discount_value.max'=>'Value maximum is 10 number',
                'discount_start'=>'Date is empty',
                'discount_end'=>'Date is empty'
            ]
        );
        if($validator->fails()){
            return response()->json([
                'status_code' => 422,
                'message' => $validator->errors()
            ],200);
        }
        try {
            $discount = Discount::create($request->all());
            if(!$discount == null){
                return response()->json([
                    'status_code' => $this->codeSuccess,
                    'data' => $discount
                ]);
            }
        }catch(Exception $e){
            return response()->json([
                'status_code' => $this->codeFails,
                'message' => 'Server error not response'
            ],$this->codeFails);
        }
    }

    // http://localhost:8000/api/admin/discount/edit/1
    public function edit($id)
    {
        try {
            $discount = Discount::findOrFail($id);
            if(!$discount == null){
                return response()->json([
                    'status_code' => $this->codeSuccess,
                    'data' => $discount
                ]);
            }
        }catch(Exception $e){
            return response()->json([
                'status_code' => $this->codeFails,
                'message' => 'Server error not response'
            ],$this->codeFails);
        }
    }

    // http://localhost:8000/api/admin/discount/update/1?discount_name=abababa&discount_type=Trade discount&discount_value=999999&discount_end=2021-08-20 23:59:00
    public function update(Request $request, $id)
    {
        $discount = Discount::findOrFail($id);
        if(strcmp($discount->discount_name,$request->discount_name)!=0){
            $validator = Validator::make($request->all(),
                [
                    'discount_name'=>'required|max:254|unique:discount,discount_name',
                    'discount_type'=>'required',
                    'discount_value'=>'required|max:10',
                    'discount_start'=>'required',
                    'discount_end'=>'required'
                ],
                [
                    'discount_name.required'=>'Discount is empty',
                    'discount_name.max'=>'Discount is maximum 254 character',
                    'discount_name.unique'=>'Discount is exist',
                    'discount_type.required'=>'Discount type is not valid',
                    'discount_value.required'=>'Discount values is not valid',
                    'discount_value.max'=>'Value maximum is 10 number',
                    'discount_start'=>'Date is empty',
                    'discount_end'=>'Date is empty'
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
            $isBool = $discount->update($request->all());
            if($isBool){
                return response()->json([
                    'status_code' => $this->codeSuccess,
                    'data' => $discount
                ]);
            }
        }catch(Exception $e){
            return response()->json([
                'status_code' => $this->codeFails,
                'message' => 'Server error not response'
            ],$this->codeFails);
        }
    }

    // http://localhost:8000/api/admin/discount/delete/1
    public function destroy($id)
    {
        $discount = Discount::findOrFail($id);
        try {
            if($discount->delete()){
                return response()->json([
                    'status_code' => $this->codeSuccess,
                    'message' => 'delete discount success',
                    'data' => $discount
                ]);
            }
        }catch(ModelNotFoundException  $e){
            return response()->json([
                'status_code' => $this->codeFails,
                'message' => 'Server error not response'
            ],$this->codeFails);
        }
    }

    // http://localhost:8000/api/admin/discount/seach?keyword=1000000
    public function seach(Request $request)
    {
        try{
            $result = [];
            if(!empty($request->keyword)){
                $result = Discount::where('id',$request->keyword)
                                ->orWhere(function($query) use ($request) {
                                    $query->whereBetween('discount_value',[0,$request->keyword])
                                            ->orWhere('discount_name','like',$request->keyword.'%')
                                            ->orWhere('discount_type','like',$request->keyword.'%')
                                            ->orWhere('discount_end','like','%'.$request->keyword.'%');
                                })
                                ->orderBy('created_at','desc')
                                ->paginate($request->pageSize);
            }else {
                $result = DB::table('discount')->orderBy('id')->paginate($request->pageSize);
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
