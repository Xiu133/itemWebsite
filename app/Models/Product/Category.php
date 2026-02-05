<?php

namespace App\Models\Product;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
     // $fillable 是「白名單」，只有這些欄位可以被批量賦值
     // 這是安全機制，防止惡意使用者偷改其他欄位
         protected $fillable = ['name','image','sort_order','is_active'];


    //定義關聯:一個category 有很多 product
    //hasMany表示一對多的關係 我這一筆資料 擁有很多對方資料
    public function products()
    {
        return $this->hasmany(Product::class);
    }

    //這是 Accessor 存取器 自訂一個虛擬屬性
    //命名規則:get+屬性名稱+Attribute
    public function getProductCountAttribute()
    {
        return $this->products()->count();//對應前端的 category.count
    }
}
