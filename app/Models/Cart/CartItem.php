<?php

namespace App\Models\Cart;

use App\Models\Product\Product;
use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    protected $table = 'cart_items';

    protected $fillable = ['cart_id', 'product_id', 'quantity', 'price'];

    protected $with = ['product'];

    public function cart()
    {
        return $this->belongsTo(Cart::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function getSubtotalAttribute()
    {
        return $this->price * $this->quantity;
    }

    // 取得商品顯示名稱
    public function getDisplayNameAttribute()
    {
        return $this->product->name;
    }

    // 取得商品圖片
    public function getImageUrlAttribute()
    {
        $image = $this->product->image;

        // 如果圖片已經是完整路徑，直接回傳
        if ($image && (str_starts_with($image, 'http') || str_starts_with($image, '/'))) {
            return $image;
        }

        // 加上 /images/ 前綴
        return $image ? '/images/' . $image : null;
    }
}
