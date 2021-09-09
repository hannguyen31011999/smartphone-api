<?php

namespace App\Http\Controllers\api\backend;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Categories;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class ApiCategoriesController extends Controller
{

    // http://localhost:8000/api/admin/categories/list
    public function index(Request $request)
    { 
        $result = DB::table('categories')
                    ->where('deleted_at','=',null)
                    ->orderBy('id')
                    ->paginate($request->pageSize);
        return response()->json([
            'status_code' => $this->codeSuccess,
            'data' => $result
        ]);
    }

    // http://localhost:8000/api/admin/categories/create?categories_name=Iphone&categories_desc=Iphone là một sản phẩm đến từ tập đoàn Apple
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(),
            [
                'categories_name'=>'required|max:254|unique:categories,categories_name',
                'categories_desc'=>'max:3000'
            ],
            [
                'categories_name.required'=>'categories is not valid',
                'categories_name.max'=>'categories is maximum 254 character',
                'categories_name.unique'=>'categories is exist',
                'categories_desc.max'=>'desc is maximum 3000 character'
            ]
        );
        if($validator->fails()){
            return response()->json([
                'status_code' => 422,
                'message' => $validator->errors()
            ],200);
        }
        try {
            $categories = Categories::create($request->all());
            if(!$categories == null){
                return response()->json([
                    'status_code' => $this->codeSuccess,
                    'data' => $categories
                ]);
            }
        }catch(Exception $e){
            return response()->json([
                'status_code' => $this->codeFails,
                'message' => 'Server error not response'
            ],$this->codeFails);
        }
    }

    // http://localhost:8000/api/admin/categories/edit/1
    public function edit($id)
    {
        try {
            $categories = Categories::findOrFail($id);
            if(!$categories == null){
                return response()->json([
                    'status_code' => $this->codeSuccess,
                    'data' => $categories
                ]);
            }
        }catch(Exception $e){
            return response()->json([
                'status_code' => $this->codeFails,
                'message' => 'Server error not response'
            ],$this->codeFails);
        }
    }

    // http://localhost:8000/api/admin/categories/update/1?categories_name=Iphone&categories_desc=abc
    public function update(Request $request, $id)
    {
        $categories = Categories::findOrFail($id);
        if(strcmp($categories->categories_name,$request->categories_name)!=0){
            $validator = Validator::make($request->all(),
                [
                    'categories_name'=>'required|max:254|unique:categories,categories_name',
                    'categories_desc'=>'max:3000'
                ],
                [
                    'categories_name.required'=>'categories is not valid',
                    'categories_name.max'=>'categories is maximum 254 character',
                    'categories_name.unique'=>'categories is exist',
                    'categories_desc.max'=>'desc is maximum 3000 character'
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
            $isBool = $categories->update($request->all());
            if($isBool){
                return response()->json([
                    'status_code' => $this->codeSuccess,
                    'data' => $categories
                ]);
            }
        }catch(Exception $e){
            return response()->json([
                'status_code' => $this->codeFails,
                'message' => 'Server error not response'
            ],$this->codeFails);
        }
    }

    // http://localhost:8000/api/admin/categories/delete/2
    public function destroy(Request $request)
    {
        $categories = Categories::findOrFail($request->id);
        try {
            if($categories->delete()){
                return response()->json([
                    'status_code' => $this->codeSuccess,
                    'message' => 'delete categories success'
                ]);
            }
        }catch(Exception $e){
            return response()->json([
                'status_code' => $this->codeFails,
                'message' => 'Server error not response',
                'data' => $categories
            ],$this->codeFails);
        }
    }

    // http://localhost:8000/api/admin/categories/seach?keyword=Iphone
    public function seach(Request $request) 
    {
        try {
            $result = null;
            if(!empty($request->keyword)){
                $result = Categories::where('id',$request->keyword)
                                ->orWhere('categories_name','like',$request->keyword.'%')
                                ->orderBy('created_at','desc')
                                ->paginate($request->pageSize);
            }else {
                $result = Categories::orderBy('id')->paginate($request->pageSize);
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
