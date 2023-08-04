<?php
/**
 * @Author: Bernard Hanna
 * @Date:   2023-07-07 10:42:21
 * @Last Modified by:   Bernard Hanna
 * @Last Modified time: 2023-07-07 12:21:32
 */


namespace App\Services;

use GuzzleHttp\Client;
use App\Models\Product;
use App\Models\Variant;
use Illuminate\Support\Facades\Log;

class ShopifyProductService
{
    private $shopifyConfigService;

    public function __construct(ShopifyConfigService $shopifyConfigService)
    {
        $this->shopifyConfigService = $shopifyConfigService;
    }

    public function syncProducts()
    {
        $shop = $this->shopifyConfigService->getShop();
        $token = $this->shopifyConfigService->getToken();
        $apiVersion = $this->shopifyConfigService->getApiVersion();

        $client = new Client();
        $products = [];
        $sinceId = null;

        do {
          $url = "https://{$shop}/admin/api/{$apiVersion}/products.json?limit=250";

          // Add the since_id parameter if available
          if ($sinceId !== null) {
              $url .= "&since_id={$sinceId}";
          }


            $response = $client->request(
                'GET',
                $url,
                ['headers' => ['X-Shopify-Access-Token' => $token]]
            );

            $responseData = json_decode($response->getBody(), true);

            if (!isset($responseData['products'])) {
                Log::error('products key not found in response', $responseData);
                dd('products key not found in response', $responseData);
            }

            $products = array_merge($products, $responseData['products']);

            if (!empty($responseData['products'])) {
                $lastProduct = end($responseData['products']);
                $sinceId = $lastProduct['id'];
            }

            Log::info('Fetched products', ['sinceId' => $sinceId, 'products' => $responseData['products']]);
            Log::info('API response', ['count' => count($responseData['products']), 'sinceId' => $sinceId]);

        } while (!empty($responseData['products']));

        foreach ($products as $product_data) {
            // Update or create the product
            try {
                $product = Product::updateOrCreate(
                    ['id' => $product_data['id']],
                    [
                        'title' => $product_data['title'],
                        'body_html' => $product_data['body_html'],
                        'vendor' => $product_data['vendor'],
                        'product_type' => $product_data['product_type'],
                        'handle' => $product_data['handle'],
                        'image' => isset($product_data['image']['src']) ? $product_data['image']['src'] : null,
                        'product_link' => "https://{$shop}/products/{$product_data['handle']}",
                        'status' => isset($product_data['status']) ? $product_data['status'] : null,
                    ]
                );
            } catch (\Exception $e) {
                Log::error('Error updating or creating product', ['product_data' => $product_data, 'error' => $e->getMessage()]);
                continue;
            }

            Log::info('Syncing product', ['productId' => $product_data['id']]);

            if (isset($product_data['variants'])) {
                foreach ($product_data['variants'] as $variant_data) {
                    // Update or create the variant
                    try {
                        Variant::updateOrCreate(
                            ['id' => $variant_data['id']],
                            [
                                'product_id' => $product->id,
                                'title' => $variant_data['title'],
                                'price' => $variant_data['price'],
                                'available' => $variant_data['inventory_quantity'] > 0,
                                'inventory' => $variant_data['inventory_quantity'], // Store the inventory quantity
                                'sku' => $variant_data['sku'],
                                'position' => $variant_data['position'],
                                'inventory_item_id' => $variant_data['inventory_item_id'], // Store the inventory item ID
                            ]
                        );
                    } catch (\Exception $e) {
                        Log::error('Error updating or creating variant', ['variant_data' => $variant_data, 'error' => $e->getMessage()]);
                        continue;
                    }
                }
            }
        }

        Log::info('Finished syncing products', ['total_products' => count($products)]);

        return count($products);
    }
}
