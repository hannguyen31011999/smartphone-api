<?php

namespace App\Http\Controllers\api\backend;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class ApiReviewController extends Controller
{

    // http://localhost:8000/api/admin/review/list?pageSize=15
    public function index(Request $request)
    {
        $result = DB::table('review')
                    ->where('deleted_at','=',null)
                    ->orderBy('id')
                    ->paginate($request->pageSize);
        return response()->json([
            'status_code' => $this->codeSuccess,
            'data' => $result
        ]);
    }

    // http://localhost:8000/api/admin/review/update/43?review_status=0
    public function update(Request $request, $id)
    {
        $review = Review::findOrFail($id);
        try {
            if($review->update($request->all())){
                return response()->json([
                    'status_code' => $this->codeSuccess,
                    'data' => $review
                ]);
            }
        }catch(Exception $e){
            return response()->json([
                'status_code' => $this->codeFails,
                'message' => 'Server error not response'
            ],$this->codeFails);
        }
    }

    // http://localhost:8000/api/admin/review/delete/43
    public function destroy($id)
    {
        $review = Review::findOrFail($id);
        try {
            if($review->delete()){
                return response()->json([
                    'status_code' => $this->codeSuccess,
                    'message' => 'Delete review success'
                ]);
            }
        }catch(Exception $e){
            return response()->json([
                'status_code' => $this->codeFails,
                'message' => 'Server error not response',
                'data' => $review
            ],$this->codeFails);
        }
    }

    // http://localhost:8000/api/admin/review/seach?keyword=nguyen
    public function seach(Request $request)
    {
        try {
            $result = null;
            if(!empty($request->keyword)){
                $result = Review::where('id',$request->keyword)
                                ->orWhere('review_name','like',$request->keyword.'%')
                                ->orWhere('review_email','like',$request->keyword.'%')
                                ->orWhere('review_phone','like',$request->keyword.'%')
                                ->orderBy('created_at','desc')
                                ->paginate($request->pageSize);
            }else {
                $result = Review::orderBy('id')->paginate($request->pageSize);
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
