<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Visitor extends Model
{
    //
    protected $table = "visitor";
    protected $fillable = ['id','ip_guest','created_at','updated_at'];
}
