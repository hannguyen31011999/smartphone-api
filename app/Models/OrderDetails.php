<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderDetails extends Model
{
    //
    protected $table = "order_details";
    protected $fillable = ['id','order_id','sku_id','product_name','product_price','qty','discount','created_at','updated_at'];

    public function Orders()
    {
        return $this->belongsTo('App\Models\Order','order_id', 'id');
    }

    public function ProductSkus()
    {
        return $this->belongsTo('App\Models\ProductSku','sku_id', 'id');
    }
}
