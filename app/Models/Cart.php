<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    protected $table = "cart";
    protected $fillable = ['id','sku_id','name','unit_price','promotion_price','color','slug','discount','qty','image','address_ip','created_at','updated_at'];
    
    public function ProductSkus()
    {
        return $this->hasMany('App\Models\ProductSku', 'sku_id', 'id');
    }
}
