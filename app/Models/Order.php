<?php
/**
 * @Author: Bernard Hanna
 * @Date:   2023-05-04 14:38:13
 * @Last Modified by:   Bernard Hanna
 * @Last Modified time: 2023-07-07 15:50:06
 */


namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $table = 'orders'; // Replace 'orders' with the actual table name for orders in your database
    protected $primaryKey = 'id';
    protected $fillable = [
        'order_reference',
        'basket_id',
        'customer_title',
        'customer_forename',
        'customer_surname',
        'customer_house_name_number',
        'customer_line_1',
        'customer_line_2',
        'customer_line_3',
        'customer_county',
        'customer_postcode',
        'customer_telephone',
        'customer_email',
        'order_line',
        'sent_to_shopify',
        'fulfilled',
        'shopify_order_id',
        'shopify_order_number',
        'cancelled_at',
        'cancel_reason',
        'rejected'
    ];

    public function productCodes()
    {
        return $this->hasMany(ProductCode::class, 'basket_id', 'basket_id');
    }
    public function fulfillments()
    {
        return $this->hasMany(Fulfillment::class, 'order_id');
    }
}
