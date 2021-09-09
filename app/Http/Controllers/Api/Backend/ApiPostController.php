<?php

namespace App\Http\Controllers\api\backend;

use JWTAuth;
use Image;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;

class ApiPostController extends Controller
{
    // http://localhost:8000/api/admin/post/list
    public function index(Request $request)
    {
        $result = DB::table('post')
                    ->where('deleted_at','=',null)
                    ->orderBy('created_at')
                    ->paginate($request->pageSize);
        return response()->json([
            'status_code' => $this->codeSuccess,
            'data' => $result
        ]);
    }

    public function uploadFilePost(Request $request){
        if($request->hasFile('image')){
            $file = $request->file('image');
            $fileName = time() . '.' . $file->getClientOriginalExtension();
            Storage::disk('post_image')->put($fileName,$file);
            return 'storage/post_image/'.$fileName;
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(),
            [
                'title'=>'required|max:254|unique:post,title',
                'content'=>'required',
                'image' => 'mimes:jpeg,jpg,png,gif|required|max:2048' 
            ],
            [
                'title.required'=>'Title is not valid!',
                'title.max'=>'Title is maximum 254 character!',
                'title.unique'=>'Title is exist!',
                'content.required'=>'Content is not valid!',
                'image.mimes' => 'Wrong image format!',
                'image.required' => 'Image is not valid!',
                'image.max'=>'Image is maximum 2Mb!'
            ]
        );
        if($validator->fails()){
            return response()->json([
                'status_code' => 422,
                'message' => $validator->errors()
            ],$this->codeSuccess);
        }
        try {
            if($request->hasFile('image')){
                $admin = JWTAuth::parseToken()->authenticate();
                $file = $request->file('image');
                $fileName = time() . '.' . $file->getClientOriginalExtension();
                $image = Image::make($file);
                // resize width 70px and height 70px
                $image->resize(334,193, function ($constraint) {
                    $constraint->aspectRatio();
                })->encode($file->getClientOriginalExtension());
                Storage::disk('post')->put($fileName,(string)$image);
                $post = Post::create([
                    'user_id' => $admin->id,
                    'title' => $request->title,
                    'content' => $request->content,
                    'image' => $fileName,
                    'url' => utf8tourl($request->title)
                ]);
                if(!empty($post)){
                    return response()->json([
                        'status_code' => $this->codeSuccess,
                        'data' => $post
                    ]);
                }
            }
        }catch(Exception $e){
            return response()->json([
                'status_code' => $this->codeFails,
                'message' => 'Server error not response'
            ],$this->codeFails);
        }
    }

    // http://localhost:8000/api/admin/post/edit/1
    public function edit($id)
    {
        try {
            $post = Post::findOrFail($id);
            return response()->json([
                'status_code' => $this->codeSuccess,
                'data' => $post
            ]);
        }catch(Exception $e){
            return response()->json([
                'status_code' => $this->codeFails,
                'message' => 'Server error not response'
            ],$this->codeFails);
        }
    }

    // http://localhost:8000/api/admin/post/update/1
    public function update(Request $request, $id)
    {
        $post = Post::findOrFail($id);
        $admin = JWTAuth::parseToken()->authenticate();
        $image = null;
        $fileName = null;
        if(strcmp($request->title,$post->title)!=0){
            if($request->hasFile('image') != true){
                $validator = Validator::make($request->all(),
                    [
                        'title'=>'required|max:254|unique:categories,categories_name',
                        'content'=>'required',
                        'image' => 'mimes:jpeg,jpg,png,gif|max:2048' 
                    ],
                    [
                        'title.required'=>'title is not valid',
                        'title.max'=>'title is maximum 254 character',
                        'title.unique'=>'title is exist',
                        'content.required'=>'content is not valid',
                        'image.mimes' => 'wrong image format',
                        'image.max'=>'image is maximum 2Mb'
                    ]
                );
                if($validator->fails()){
                    return response()->json([
                        'status_code' => 422,
                        'message' => $validator->errors()
                    ],$this->codeSuccess);
                }
            }
        }
        try{
            if($request->hasFile('image')){
                if (Storage::disk('post')->exists($post->image)) {
                    Storage::disk('post')->delete($post->image);
                }
                $file = $request->file('image');
                $fileName = time() . '.' . $file->getClientOriginalExtension();
                $image = Image::make($file);
                // resize width 70px and height 70px
                $image->resize(70,70, function ($constraint) {
                    $constraint->aspectRatio();
                })->encode($file->getClientOriginalExtension());
                Storage::disk('post')->put($fileName,(string)$image);
            }
            $post->update([
                'user_id' => $admin->id,
                'title' => $request->title,
                'content' => $request->content,
                'image' => !empty($fileName) ? $fileName : $post->image,
                'url' => utf8tourl($request->title)
            ]);
            if(!empty($post)){
                return response()->json([
                    'status_code' => $this->codeSuccess,
                    'data' => $post
                ]);
            }
        }catch(Exception $e){
            return response()->json([
                'status_code' => $this->codeFails,
                'message' => 'Server error not response'
            ],$this->codeFails);
        }
    }

    // http://localhost:8000/api/admin/post/delete/1
    public function destroy($id)
    {
        try {
            $post = Post::findOrFail($id);
            if($post->delete()){
                if (Storage::disk('post')->exists($post->image)) {
                    Storage::disk('post')->delete($post->image);
                }
                return response()->json([
                    'status_code' => $this->codeSuccess,
                    'message' => 'delete post success'
                ]);
            }
        }catch(Exception $e){
            return response()->json([
                'status_code' => $this->codeFails,
                'message' => 'Server error not response'
            ],$this->codeFails);
        }
    }

    // http://localhost:8000/api/admin/post/seach?keyword=Nguyễn Việt Hân
    public function seach(Request $request)
    {
        try {
            $result = [];
            if(!empty($request->keyword)){
                $result = DB::table('post as po')
                        ->leftJoin('user as us','po.user_id','=','us.id')
                        ->select('po.*')
                        ->where('po.id','=',(int)$request->keyword)
                        ->orWhere(function($query) use($request){
                            $query->where('po.title','like',$request->keyword.'%')
                                ->orWhere('us.name','like',$request->keyword.'%');
                        })
                        ->orderBy('created_at','desc')
                        ->paginate($request->pageSize);
            }else {
                $result = Post::orderBy('id')->paginate(10);
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
