<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductSku extends Model
{
    use SoftDeletes;
    protected $table = "product_skus";
    protected $fillable = ['id','product_id','product_variant_id','sku_unit_price','sku_promotion_price','sku_qty','sku_image','color','created_at','updated_at','deleted_at'];

    public function Products()
    {
        return $this->belongsTo('App\Models\Product','product_id', 'id');
    }

    public function ProductVariants()
    {
        return $this->belongsTo('App\Models\ProductVariant','product_variant_id', 'id');
    }

    public function InventoryManagements()
    {
        return $this->hasMany('App\Models\InventoryManagement', 'sku_id', 'id');
    }

    public function OrderDetails()
    {
        return $this->hasMany('App\Models\OrderDetails', 'sku_id', 'id');
    }
}
