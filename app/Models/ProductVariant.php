<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class ProductVariant extends Model
{
    use SoftDeletes;
    protected $table = "product_variant";
    protected $fillable = ['id','product_id','product_variant_name','product_variant_rom','product_variant_ram','created_at','updated_at','deleted_at'];

    public function Products()
    {
        return $this->belongsTo('App\Models\Product','product_id', 'id');
    }

    public function ProductImages()
    {
        return $this->hasMany('App\Models\ProductImage', 'product_variant_id', 'id');
    }

    public function InventoryManagements()
    {
        return $this->hasMany('App\Models\InventoryManagement', 'variant_id', 'id');
    }

    public function ProductSkus()
    {
        return $this->hasMany('App\Models\ProductSku', 'product_variant_id', 'id');
    }

    public function Slugs()
    {
        return $this->hasMany('App\Models\Slug', 'product_variant_id', 'id');
    }

    public function Reviews()
    {
        return $this->hasMany('App\Models\Review', 'product_variant_id', 'id');
    }
}
