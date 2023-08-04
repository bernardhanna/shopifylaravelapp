<?php
/**
 * @Author: Bernard Hanna
 * @Date:   2023-05-08 11:17:37
 * @Last Modified by:   Bernard Hanna
 * @Last Modified time: 2023-05-10 12:17:32
 */

 namespace App\Models;

 use Illuminate\Database\Eloquent\Model;

 class ProductCode extends Model
 {
     protected $table = 'product_codes';
        protected $fillable = [
            'basket_id',
            'basket_item_id',
            'code',
            'quantity',
            'stockist'
        ];

     public function order()
     {
        return $this->belongsTo(Order::class, 'basket_id');
     }
 }
