<?php
/**
 * @Author: Bernard Hanna
 * @Date:   2023-07-07 15:07:00
 * @Last Modified by:   Bernard Hanna
 * @Last Modified time: 2023-07-07 15:17:33
 */
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Fulfillment extends Model
{
    use HasFactory;

    protected $fillable = [
        'shopify_fulfillment_id',
        'order_id',
        'tracking_number',
        'tracking_url',
        'carrier_name',
        'carrier_code',
        'dispatched',
        'dispatched_at',
        'dipatch_number',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }
}
