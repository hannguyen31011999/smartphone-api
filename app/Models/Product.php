<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;
    protected $table = "product";
    protected $fillable = ['id','categories_id','discount_id','product_name','product_desc','created_at','updated_at','deleted_at'];

    public function Categories()
    {
        return $this->belongsTo('App\Models\Categories','categories_id', 'id');
    }

    public function Discounts()
    {
        return $this->belongsTo('App\Models\Discount','discount_id', 'id');
    }

    public function ProductOptions()
    {
        return $this->hasMany('App\Models\ProductOption', 'product_id', 'id');
    }

    public function ProductVariants()
    {
        return $this->hasMany('App\Models\ProductVariant', 'product_id', 'id');
    }

    public function ProductSkus()
    {
        return $this->hasMany('App\Models\ProductSku', 'product_id', 'id');
    }

    public function InventoryManagements()
    {
        return $this->hasMany('App\Models\InventoryManagement', 'product_id', 'id');
    }

    public function Slugs()
    {
        return $this->hasMany('App\Models\Slug', 'product_id', 'id');
    }
}
