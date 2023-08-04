<?php
/**
 * @Author: Bernard Hanna
 * @Date:   2023-05-11 16:23:31
 * @Last Modified by:   Bernard Hanna
 * @Last Modified time: 2023-05-24 13:04:24
 */
namespace App\Http\Controllers;

use App\Services\ShopifyConfigService;
use App\Services\ShopifyFulfillmentService;
use App\Services\ShopifyCancelledOrdersService;

class ShopifyFulfillmentController extends Controller
{
    private $shopifyFulfillmentService;
    private $shopifyCancelledOrdersService;

    public function __construct(
        ShopifyFulfillmentService $shopifyFulfillmentService,
        ShopifyCancelledOrdersService $shopifyCancelledOrdersService
    ) {
        $this->shopifyFulfillmentService = $shopifyFulfillmentService;
        $this->shopifyCancelledOrdersService = $shopifyCancelledOrdersService;
    }

    public function syncFulfillments()
    {
        return $this->shopifyFulfillmentService->syncFulfillments();
    }

    public function syncCancelledOrders()
    {
        return $this->shopifyCancelledOrdersService->syncCancelledOrders();
    }
}