<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Slug extends Model
{
    //
    protected $table = "slug";
    protected $fillable = ['id','product_id','product_variant_id','slug_url','created_at','updated_at'];

    public function Products()
    {
        return $this->belongsTo('App\Models\Product','product_id', 'id');
    }

    public function ProductVariants()
    {
        return $this->belongsTo('App\Models\ProductVariant','product_variant_id', 'id');
    }
}
