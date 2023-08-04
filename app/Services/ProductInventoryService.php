<?php
/**
 * @Author: Bernard Hanna
 * @Date:   2023-05-30 16:07:58
 * @Last Modified by:   Bernard Hanna
 * @Last Modified time: 2023-05-30 16:15:36
 */
namespace App\Services;

use App\Models\Variant;
use App\Models\ProductCode;
use Illuminate\Support\Facades\Log;

class ProductInventoryService
{
  public function updateProductInventory($order)
  {
      foreach ($order->productCodes as $orderProduct) {
          $productCode = ProductCode::where('code', $orderProduct->code)->first();

          if ($productCode) {
              $productCode->decrement('quantity', $orderProduct->quantity);
          }
      }
  }
}
