<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use Notifiable;

    protected $table = "user";

    protected $fillable = [
        'name', 'email', 'password','name','gender','birth','phone','address','status','role','remember_token','created_at','updated_at','deleted_at'
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }
    public function getJWTCustomClaims()
    {
        return [];
    }

    public function Posts()
    {
        return $this->hasMany('App\Models\Post', 'user_id', 'id');
    }

    public function Orders()
    {
        return $this->hasMany('App\Models\Order', 'user_id', 'id');
    }

    public function Carts()
    {
        return $this->hasMany('App\Models\Cart', 'user_id', 'id');
    }

    public function Reviews()
    {
        return $this->hasMany('App\Models\Review', 'user_id', 'id');
    }

    public function ChatMessages()
    {
        return $this->hasMany('App\Models\Messages', 'user_id', 'id');
    }
}
