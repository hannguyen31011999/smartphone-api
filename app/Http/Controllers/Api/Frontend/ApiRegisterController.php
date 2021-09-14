<?php

namespace App\Http\Controllers\Api\Frontend;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Hash;
use Tymon\JWTAuth\Exceptions\JWTException;
use Carbon\Carbon;
use JWTAuth;

class ApiRegisterController extends Controller
{
    public $loginAfterSignUp = true;
    private $expired = 60;
    // http://localhost:8000/api/register/create?name=Nguyễn Văn Thể&email=thenguyen3199@gmail.com&password=123456&confirm_password=123456&phone=0382484477&address=đường 3/2 phường 7, quận 10&birth=1993-07-14&gender=1
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(),
            [
                'name'=>'required|regex:[^[a-zA-Z]]|max:254',
                'email'=>'required|email|unique:user,email|max:254',
                'password'=>'required|min:6|max:254',
                'confirm_password'=>'required|same:password',
                'address'=>'required|max:254',
                'phone'=>'required|regex:/(0)[0-9]{9}/'
            ],
            [
                'phone.regex'=>'Number phone start 0 and maximum 10 number'
            ]
        );
        if($validator->fails()){
            return response()->json([
                'status_code' => 422,
                'message' => $validator->errors()
            ]);
        }
        try {
            $input = $request->all();
            $input['status'] = 1;
            $input['role'] = 1;
            $input['password'] = Hash::make($input['password']);
            $result = User::create($input);
            if($result){
                return response()->json([
                    'status_code' => $this->codeSuccess,
                    'data' => $result
                ]);
            }
        }catch(Exception $e){
            return response()->json([
                'status_code' => $this->codeFails,
                'message' => 'Server error not response'
            ],$this->codeFails);
        }
    }

    public function update(Request $request,$id)
    {

    }
}
