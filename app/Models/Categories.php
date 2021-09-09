<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Categories extends Model
{
    use SoftDeletes;
    
    protected $table = "categories";
    protected $fillable = ['id','categories_name','categories_desc','created_at','updated_at','deleted_at'];
    
    public function Products()
    {
        return $this->hasMany('App\Models\Product', 'categories_id', 'id');
    }
}
