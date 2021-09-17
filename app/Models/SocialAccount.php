<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SocialAccount extends Model
{
    protected $table = "social_accounts";
    protected $fillable = ['user_id','provider_user_id','provider','created_at','updated_at'];

    public function Users()
    {
        return $this->hasMany('App\Models\User','user_id', 'id');
    }

}
