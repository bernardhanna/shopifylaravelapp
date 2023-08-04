<?php
/**
 * @Author: Bernard Hanna
 * @Date:   2023-05-24 13:17:42
 * @Last Modified by:   Bernard Hanna
 * @Last Modified time: 2023-05-24 13:17:47
 */
namespace App\Services;

use App\Models\Order;
use App\Models\ProductCode;

class GarbageCollectionService
{
    public function performGarbageCollection()
    {
        // Delete orders and product_codes that are over 90 days old.
        $cutoffDate = now()->subDays(90);
        Order::where('created_at', '<', $cutoffDate)->delete();
        ProductCode::where('created_at', '<', $cutoffDate)->delete();
    }
}