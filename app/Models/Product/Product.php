<?php

namespace App\Models\Product;

use App\Models\Front\Brand;
use App\Models\Cart\CartItem;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'user_id',
        'category_id',
        'brand_id',
        'name',
        'description',
        'price',
        'original_price',
        'image',
        'stock',
        'is_active'
    ];

    //定義關聯 product"屬於"一個category
    //belongsTo是hasmany的反向
      public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // 多對多關聯：一個商品可以有多個標籤
    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'product_tag');
    }

    //一個商品可以出現在多個購物車項目中
    public function cartItem()
    {
        return $this->hasMany(CartItem::class);
    }

    public function getOnSaleAttribute()
    {
        return $this->original_price && $this->original_price > $this->price;
    }
    //自訂屬性 判斷是否特價中
    //使用方式:
    //$product->on_sale; //true or false
    //如果有原價 且原價大於現價 就是特價中
    //先確定原價有設定 然後原價價格有大於現價嗎
}
