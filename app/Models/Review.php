<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    //

    protected $table = "review";
    protected $fillable = ['id','user_id','product_variant_id','review_name','review_email','review_star','review_content','review_phone','review_status','created_at','updated_at','deleted_at'];

    public function Users()
    {
        return $this->belongsTo('App\Models\User','user_id', 'id');
    }

    public function ProductVariants()
    {
        return $this->belongsTo('App\Models\ProductVariant','product_variant_id', 'id');
    }
}
