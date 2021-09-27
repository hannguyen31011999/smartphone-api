<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $table = "order";
    protected $fillable = ['id','user_id','transport_price','order_note','order_email','order_name','order_address','order_phone','order_status','order_payment','payment_option','created_at','updated_at','deleted_at'];

    public function Users()
    {
        return $this->belongsTo('App\Models\User','user_id', 'id');
    }

    public function OrderDetails()
    {
        return $this->hasMany('App\Models\OrderDetails','order_id','id');
    }
}
