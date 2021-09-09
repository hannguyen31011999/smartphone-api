<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Post extends Model
{
    use SoftDeletes;
    protected $table = "post";
    protected $fillable = ['id','user_id','title','content','image','url','created_at','updated_at','deleted_at'];

    public function Users()
    {
        return $this->belongsTo('App\Models\User','user_id', 'id');
    }
}
