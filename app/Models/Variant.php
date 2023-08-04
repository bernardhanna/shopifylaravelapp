<?php
/**
 * @Author: Bernard Hanna
 * @Date:   2023-05-11 11:18:36
 * @Last Modified by:   Bernard Hanna
 * @Last Modified time: 2023-06-07 10:05:41
 */


namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Variant extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'product_id',
        'title',
        'price',
        'available',
        'inventory',
        'sku',
        'position',
        'inventory_item_id',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
