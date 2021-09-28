<?php

namespace App\Http\Controllers\api\backend;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Events\MessagesEvent;
use App\Models\Messages;
use Carbon\Carbon;
use Event;

class ApiMessageController extends Controller
{
    public function createMessage(Request $request)
    {
        try {
            $mess = Messages::create($request->all());
            if(!empty($mess)){
                event(new MessagesEvent($mess->messages));
                return response()->json([
                    'status_code'=>$this->codeSuccess,
                ]);
            }
        }catch(Exception $e){
            return response()->json([
                'status_code'=>$this->codeFails
            ],$this->codeFails);
        }
    }
}
