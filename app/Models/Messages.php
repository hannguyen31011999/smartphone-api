<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Messages extends Model
{
    protected $table = "messages";
    protected $fillable = ['id','user_id','messages','created_at','updated_at'];
    
    public function Users()
    {
        return $this->belongsTo('App\Models\User', 'user_id', 'id');
    }
}
