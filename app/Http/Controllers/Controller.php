<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController
{
    protected $codeSuccess = 200;
    protected $codeFails = 500;
    protected $codeEmpty = 300;
    protected $expiredAt = 5;
    
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
}
