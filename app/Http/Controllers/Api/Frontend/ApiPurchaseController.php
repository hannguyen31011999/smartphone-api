<?php

namespace App\Http\Controllers\api\frontend;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Models\User;

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
                'status_code'=>$this->codeSuccess,
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

    }
}
