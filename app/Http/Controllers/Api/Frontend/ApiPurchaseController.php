<?php

namespace App\Http\Controllers\api\frontend;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Hash;

class ApiPurchaseController extends Controller
{
    public function updateUser(Request $request,$id)
    {
        $validator = Validator::make($request->all(),
            [
                'name'=>'required|max:100|regex:[^[a-zA-Z]]',
                'address'=>'required|max:254',
                'phone'=>'required|regex:/(0)[0-9]{9}/'
            ]
        );
        if($validator->fails()){
            return response()->json([
                'status_code'=>$this->codeFails,
                'data'=>$validator->errors()
            ]);
        }
        try{
            $user = User::findOrFail($id);
            $isBool = $user->update($request->all());
            if($isBool){
                return response()->json([
                    'status_code'=>$this->codeSuccess,
                    'data'=>$user
                ]);
            }
        }catch(Exception $e){
            return response()->json([
                'status_code'=>$this->codeFails
            ]);
        }
    }

    public function updatePassword(Request $request,$id)
    {
        $user = User::findOrFail($id);
        $validator = Validator::make($request->all(),
            [
                'new_password'=>'required|min:6|max:254',
                'same_password'=>'required|same:new_password',
            ]
        );
        if($validator->fails() || !Hash::check($request->current_password,$user->password)){
            $isBool = Hash::check($request->current_password,$user->password);
            return response()->json([
                'status_code'=>$this->codeFails,
                'data'=>$validator->errors() ? $validator->errors() : null,
                'message'=>$isBool ? null : 'Current password is wrong'
            ]);
        }
        try{
            $input["password"] = Hash::make($request->new_password);
            $isBool = $user->update($input);
            if($isBool){
                return response()->json([
                    'status_code'=>$this->codeSuccess,
                    'data'=>$user
                ]);
            }
        }catch(Exception $e){
            return response()->json([
                'status_code'=>$this->codeFails
            ]);
        }
    }

    // http://localhost:8000/api/user/3/purchase/all
    public function getAllPurchase(Request $request,$id)
    {
        try{
            $user = User::findOrFail($id);
            $order = $user->Orders()->with(['OrderDetails' => function($query){
                $query->with('ProductSkus')->get();
            }])->paginate(3);
            return response()->json([
                'status_code'=>$this->codeSuccess,
                'data'=>$order
            ]);
        }catch(Exception $e){
            return response()->json([
                'status_code'=>$this->codeFails
            ]);
        }
    }

    // http://localhost:8000/api/user/3/purchase?type=1
    public function getPurchaseForStatus(Request $request,$id)
    {
        try{
            $user = User::findOrFail($id);
            $order = $user->Orders()
                        ->where('order_status','=',$request->type)
                        ->with(['OrderDetails' => function($query){
                            $query->with('ProductSkus')->get();
                        }])
                        ->paginate(3);
            return response()->json([
                'status_code'=>$this->codeSuccess,
                'data'=>$order
            ]);
        }catch(Exception $e){
            return response()->json([
                'status_code'=>$this->codeFails
            ]);
        }
    }
}
