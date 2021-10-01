<?php

namespace App\Http\Controllers\api\frontend;
use JWTAuth;
use App\Models\User;
use App\Models\SocialAccount;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\Services\SocialAccountService;
use App\Events\MessagesEvent;
use Carbon\Carbon;
use Socialite;

class ApiLoginClientController extends Controller
{
    public $loginAfterSignUp = true;
    private $expired = 60*24*30;

    // http://localhost:8000/api/login
    public function login(Request $request)
    {
        $input = $request->only('email', 'password');
        JWTAuth::factory()->setTTL($this->expired);
        if (!$token = JWTAuth::attempt($input)) {
            return response()->json([
                'status_code' => 500,
                'message' => 'Invalid Email or Password',
            ]);
        }
        $data = [
            'id'=>JWTAuth::user()->id,
            'name'=>JWTAuth::user()->name,
            'email'=>JWTAuth::user()->email,
            'phone'=>JWTAuth::user()->phone,
            'address'=>JWTAuth::user()->address,
            'birth'=>JWTAuth::user()->birth,
            'gender'=>JWTAuth::user()->gender
        ];
        return response()->json([
            'status_code' => 200,
            'token' => $token ,
            'user'=> $data,
            'timestamp' => [
                'expired' => $this->expired,
                'time' => Carbon::now()
            ]
        ]);
    }

    public function refreshToken(Request $request)
    {
        JWTAuth::factory()->setTTL($this->expired);
        $token = JWTAuth::parseToken()->refresh();
        return response()->json([
            'status_code' => 200,
            'token' => $token,
            'timestamp' => [
                'expired' => $this->expired,
                'time' => Carbon::now()
            ]
        ]);
    }

    public function logout(Request $request)
    {
        try {
            event(new MessagesEvent("",1,JWTAuth::user()->name,Carbon::now(),JWTAuth::user()->id,true));
            JWTAuth::invalidate(JWTAuth::parseToken());
            return response()->json([
                'status_code' => 200,
                'message' => 'User logged out successfully'
            ]);
        } catch (JWTException $exception) {
            return response()->json([
                'status_code' => 500,
                'message' => 'Sorry, the user cannot be logged out'
            ], 500);
        }
    }

    public function redirect($social)
    {
        return Socialite::driver($social)->redirect();
    }

    public function callback(SocialAccountService $service,$social)
    {
        try{
            $user = $service->createOrGetUser(Socialite::driver($social)->user(),$social);
            $token = JWTAuth::fromUser($user);
            $data = [
                'id'=>$user->id,
                'name'=>$user->name,
                'email'=>$user->email,
                'phone'=>$user->phone,
                'address'=>$user->address,
                'birth'=>$user->birth,
                'gender'=>$user->gender
            ];
            return response()->json([
                'status_code'=>$this->codeSuccess,
                'token'=>$token,
                'data'=>$data,
                'timestamp' => [
                    'expired' => $this->expired,
                    'time' => Carbon::now()
                ]
            ]);
        }catch (JWTException $exception) {
            return response()->json([
                'status_code' => 500,
                'message' => 'Sorry, the user cannot be logged out'
            ], 500);
        }
    }
}
