<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    //
    protected $table = "order";
    protected $fillable = ['id','user_id','transport_id','order_note','order_email','order_name','order_phone','order_status','created_at','updated_at','deleted_at'];

    public function Users()
    {
        return $this->belongsTo('App\Models\User','user_id', 'id');
    }

    public function Wards()
    {
        return $this->belongsTo('App\Models\Ward','transport_id', 'id');
    }
}
