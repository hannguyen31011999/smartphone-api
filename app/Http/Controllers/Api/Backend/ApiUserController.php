<?php

namespace App\Http\Controllers\api\backend;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class ApiUserController extends Controller
{
    private $isRole = 1;
    private $isStatus = 1;

    // http://localhost:8000/api/admin/user/list
    public function index(Request $request)
    {
        $result = DB::table('user')
                    ->where('role','=',$this->isRole)
                    ->orderBy('created_at')
                    ->paginate($request->pageSize);
        return response()->json([
            'status_code' => $this->codeSuccess,
            'data' => $result
        ]);
    }

    // http://localhost:8000/api/admin/user/create?email=xethongs100@gmail.com&password=123456&name=Nguyễn Thị Kim Hồng&address=đường Thống Nhất, Thủ Đức&phone=0322223333
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(),
            [
                'email'=>'required|email|max:100|unique:user,email',
                'password'=>'required|min:6|max:254',
                'name'=>'required|regex:[^[a-zA-Z]]|max:100',
                'phone'=>'required|numeric|regex:/(0)[0-9]{9}/',
                'address'=>'required|max:254'
            ],
            [
                'email.required'=>'email is not valid',
                'email.max'=>'email is maximum 100 character',
                'email.unique'=>'email is exist',
                'email.email'=>'email wrong format',
                'password.required'=>'password is not valid',
                'password.min'=>'password minimum is 6 character',
                'password.max'=>'password maximum is 100 character',
                'phone.required'=>'phone is not valid',
                'phone.regex'=>'phone must start 0 and maximum 10 number',
                'phone.numeric'=>'phone must be numeric',
                'address.required'=>'address is not valid',
                'address.max'=>'address maximum is 254 character'
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
            $input["birth"] = !empty($input["birth"]) ? $input["birth"] : Carbon::now();
            $input['role'] = $this->isRole;
            $input['status'] = $this->isStatus;
            $input['password'] = Hash::make($input['password']);
            $result = User::create($input);
            if(!$result == null){
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

    // http://localhost:8000/api/admin/user/edit/3
    public function edit($id)
    {
        try {
            $result = User::where('role','=',1)
                    ->where('id','=',$id)
                    ->first();
            $code = $this->codeEmpty;
            if(!empty($result)){
                $code = $this->codeSuccess;
            }
            return response()->json([
                'status_code' => $code,
                'data' => $result
            ]);
        }catch(Exception $e){
            return response()->json([
                'status_code' => $this->codeFails,
                'message' => 'Server error not response'
            ],$this->codeFails);
        }
    }

    // http://localhost:8000/api/admin/user/update/3?email=xethongs100@gmail.com&password=123456&name=Nguyễn Thị Kim Hồng&address=đường Thống Nhất, Thủ Đức&phone=0322223333
    public function update(Request $request, $id)
    {
        $user = User::where('role','=',1)
                    ->where('id','=',$id)
                    ->first();
        $code = $this->codeEmpty;
        if(strcmp($request->email,$user->email)!=0){
            $validator = Validator::make($request->all(),
                [
                    'email'=>'required|email|max:100|unique:user,email',
                    'password'=>'required|min:6|max:254',
                    'name'=>'required|regex:[^[a-zA-Z]]|max:100',
                    'phone'=>'required|numeric|regex:/(0)[0-9]{9}/',
                    'address'=>'required|max:254'
                ],
                [
                    'email.required'=>'email is not valid',
                    'email.max'=>'email is maximum 100 character',
                    'email.unique'=>'email is exist',
                    'email.email'=>'email wrong format',
                    'password.required'=>'password is not valid',
                    'password.min'=>'password minimum is 6 character',
                    'password.max'=>'password maximum is 100 character',
                    'phone.required'=>'phone is not valid',
                    'phone.regex'=>'phone must start 0 and maximum 10 number',
                    'phone.numeric'=>'phone must be numeric',
                    'address.required'=>'address is not valid',
                    'address.max'=>'address maximum is 254 character'
                ]
            );
            if($validator->fails()){
                return response()->json([
                    'status_code' => 422,
                    'message' => $validator->errors()
                ]);
            }
        }
        try {
            if(!empty($user)){
                $input = $request->all();
                $input['password'] = Hash::make($input['password']);
                $isBool = $user->update($input);
                $code = ($isBool) ? $this->codeSuccess : $this->codeEmpty;
            }
            return response()->json([
                'status_code' => $code,
                'data' => $user
            ]);
        }catch(Exception $e){
            return response()->json([
                'status_code' => $this->codeFails,
                'message' => 'Server error not response'
            ],$this->codeFails);
        }
    }

    // http://localhost:8000/api/admin/user/delete/45
    public function destroy($id)
    {
        $user = User::where('role','=',1)
                    ->where('id','=',$id)
                    ->first();
        $code = $this->codeEmpty;
        try {
            if(!empty($user)){
                $isBool = $user->delete();
                $code = ($isBool) ? $this->codeSuccess : $this->codeEmpty;
            }
            return response()->json([
                'status_code' => $code,
                'message' => "Delete success"
            ]);
        }catch(Exception $e){
            return response()->json([
                'status_code' => $this->codeFails,
                'message' => 'Server error not response',
                'data' => $user
            ],$this->codeFails);
        }
    }

    // http://localhost:8000/api/admin/user/seach?keyword=0322
    public function seach(Request $request)
    {
        try {
            $result = [];
            if(!empty($request->keyword)){
                $result = User::where('role','=',1)
                            ->where('id',$request->keyword)
                            ->orWhere(function($query) use ($request){
                                $query->where('email','like',$request->keyword.'%')
                                    ->orWhere('name','like',$request->keyword.'%')
                                    ->orWhere('phone','like',$request->keyword.'%');
                            })
                            ->orderBy('created_at','desc')
                            ->paginate($request->pageSize);
            }else {
                $result = DB::table('user')
                    ->where('status','=',$this->isStatus)
                    ->where('role','=',$this->isRole)
                    ->orderBy('created_at')
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

    // http://localhost:8000/api/admin/user/status/3?status=2
    public function updateStatus(Request $request,$id)
    {
        // 1 active 2 block
        try {
            $user = User::where('role','=',1)
                    ->where('id','=',$id)
                    ->first();
            if(!empty($user) && !empty($request->status)){
                $isBool = $user->update($request->all());
            }
            return response()->json([
                'status_code' => $this->codeSuccess,
                'data' => $user
            ]);
        }catch(Exception $e){
            return response()->json([
                'status_code' => $this->codeFails,
                'message' => 'Server error not response'
            ],$this->codeFails);
        }
    }
}
