<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Discount extends Model
{
    use SoftDeletes;
    protected $table = "discount";
    protected $fillable = ['discount_name','discount_type','discount_value','discount_start','discount_end','created_at','updated_at','deleted_at'];

    public function Products()
    {
        return $this->hasMany('App\Models\Product', 'discount_id', 'id');
    }
}
