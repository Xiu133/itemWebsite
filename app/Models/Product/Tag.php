<?php

namespace App\Models\Product;

use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'color',
        'icon',
        'is_active',
        'sort_order'
    ];

    // 多對多關聯：一個標籤可以屬於多個商品
    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_tag');
    }
}
