<?php

namespace App\Models\Front;

use App\Models\Product\Product;
use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    protected $fillable = ['name','logo'];

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
