<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InventoryManagement extends Model
{
    protected $table = "inventory_management";
    protected $fillable = [
        'id',
        'product_id',
        'variant_id',
        'sku_id',
        'unit_price',
        'promotion_price',
        'qty',
        'status',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    public function Products()
    {
        return $this->belongsTo('App\Models\Product','product_id', 'id');
    }

    public function ProductSkus()
    {
        return $this->belongsTo('App\Models\ProductSku','sku_id', 'id');
    }

    public function ProductVariants()
    {
        return $this->belongsTo('App\Models\ProductVariant','variant_id', 'id');
    }
}
