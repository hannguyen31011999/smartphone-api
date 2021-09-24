<?php

namespace App\Http\Controllers\Api\Backend;

use JWTAuth;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Tymon\JWTAuth\Exceptions\JWTException;
use Carbon\Carbon;

class ApiLoginController extends Controller
{
    public $loginAfterSignUp = true;
    private $expired = 60*24*30;

    // http://localhost:8000/api/login?email=admin1@gmail.com&password=123456
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
        return response()->json([
            'status_code' => 200,
            'token' => $token ,
            'user'=> JWTAuth::user(),
            'timestamp' => [
                'expired' => $this->expired,
                'time' => Carbon::now()
            ]
        ]);
    }

    // http://localhost:8000/api/refresh/token
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

    public function getAdminInfo(Request $request)
    {
        return response()->json([
            'status_code' => 200,
            'user'=> JWTAuth::user()
        ]);
    }

    // http://localhost:8000/api/logout?token=
    public function logout(Request $request)
    {
        try {
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
}
