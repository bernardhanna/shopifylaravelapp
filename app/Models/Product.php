<?php
/**
 * @Author: Bernard Hanna
 * @Date:   2023-05-11 11:18:26
 * @Last Modified by:   Bernard Hanna
 * @Last Modified time: 2023-06-06 11:37:38
 */


namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'title',
        'body_html',
        'vendor',
        'product_type',
        'handle',
        'image',
        'product_link',
        'status'
    ];

    public function variants()
    {
        return $this->hasMany(Variant::class, 'product_id');
    }
}
