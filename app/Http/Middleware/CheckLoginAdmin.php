<?php

namespace App\Http\Middleware;

use Closure;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class CheckLoginAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $isCheck = JWTAuth::parseToken()->authenticate();
        if($isCheck->role != 2){
            return response()->json([
                'status_code' => 401,
                'message' => 'You can not access api',
            ],200);
        }
        return $next($request);
    }
}
