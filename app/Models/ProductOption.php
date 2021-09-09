<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductOption extends Model
{
    use SoftDeletes;
    protected $table = "product_option";
    protected $fillable = ['id','product_id','screen','screen_resolution','operating_system','cpu','gpu','ram','camera','video','pin','created_at','updated_at','deleted_at'];

    public function Products()
    {
        return $this->belongsTo('App\Models\Product','product_id', 'id');
    }
}
