<?php
/**
 * @Author: Bernard Hanna
 * @Date:   2023-05-11 11:22:18
 * @Last Modified by:   Bernard Hanna
 * @Last Modified time: 2023-05-30 10:49:41
 */
namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use App\Services\ShopifyProductService;

class ShopifyProductController extends Controller
{
    private $shopifyProductService;

    public function __construct(ShopifyProductService $shopifyProductService)
    {
        $this->shopifyProductService = $shopifyProductService;
    }

    public function syncShopifyProducts()
    {
        $syncedProductCount = $this->shopifyProductService->syncProducts();

        if ($syncedProductCount > 0) {
            session()->flash('success', "{$syncedProductCount} products synced");
        } else {
            session()->flash('info', 'No products to sync');
        }

        return redirect()->back();
    }
}
