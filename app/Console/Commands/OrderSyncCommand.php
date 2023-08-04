<?php
/**
 * @Author: Bernard Hanna
 * @Date:   2023-05-24 14:42:17
 * @Last Modified by:   Bernard Hanna
 * @Last Modified time: 2023-06-22 09:35:28
 */
namespace App\Console\Commands;

use App\Services\ShopifyProductService;
use App\Services\OrderSyncService;
use App\Services\CreateOrderService;
use App\Services\ShopifyFulfillmentService;
use App\Services\ShopifyCancelledOrdersService;
use App\Services\GarbageCollectionService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class OrderSyncCommand extends Command
{
    protected $signature = 'orders:sync';
    protected $description = 'Sync orders from SWSM API to the database and send them to Shopify';

    private $shopifyProductService;
    private $orderSyncService;
    private $createOrderService;
    private $shopifyFulfillmentService;
    private $shopifyCancelledOrdersService;
    private $garbageCollectionService;

    public function __construct(
        ShopifyProductService $shopifyProductService,
        OrderSyncService $orderSyncService,
        CreateOrderService $createOrderService,
        ShopifyFulfillmentService $shopifyFulfillmentService,
        ShopifyCancelledOrdersService $shopifyCancelledOrdersService,
        GarbageCollectionService $garbageCollectionService
    ) {
        parent::__construct();

        $this->shopifyProductService = $shopifyProductService;
        $this->orderSyncService = $orderSyncService;
        $this->createOrderService = $createOrderService;
        $this->shopifyFulfillmentService = $shopifyFulfillmentService;
        $this->shopifyCancelledOrdersService = $shopifyCancelledOrdersService;
        $this->garbageCollectionService = $garbageCollectionService;
    }

    public function handle()
    {
        try {
            // Step 1: Sync products from Shopify to the database
            $this->shopifyProductService->syncProducts();

            // Step 2: Sync orders from SWSM API to the database
            $this->orderSyncService->syncOrders();

            // Step 3: Send orders to Shopify
            $this->createOrderService->sendOrdersToShopify();

            // Step 4: Fulfill orders
            $this->shopifyFulfillmentService->syncFulfillments();

            // Step 5: Sync canceled orders
            $this->shopifyCancelledOrdersService->syncCancelledOrders();

            // Step 6: Garbage collection
            $this->garbageCollectionService->performGarbageCollection();

            $this->info('Order synchronization completed successfully.');
        } catch (\Exception $e) {
            // Handle any exceptions that occur during the synchronization process
            $this->error('An error occurred during order synchronization: ' . $e->getMessage());
            // You can also log the exception for further investigation
            Log::error('Order synchronization error: ' . $e->getMessage());
        }
    }
}