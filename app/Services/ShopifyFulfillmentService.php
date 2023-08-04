<?php
/**
 * @Author: Bernard Hanna
 * @Date:   2023-07-06 17:19:27
 * @Last Modified by:   Bernard Hanna
 * @Last Modified time: 2023-07-07 16:58:03
 */


namespace App\Services;

use GuzzleHttp\Client;
use App\Models\Order;
use App\Models\Fulfillment;
use App\Mail\FulfillmentOrdersMail;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class ShopifyFulfillmentService
{
    private $shopifyConfigService;

    public function __construct(ShopifyConfigService $shopifyConfigService)
    {
        $this->shopifyConfigService = $shopifyConfigService;
    }

    public function syncFulfillments()
    {
        try {
            // Get shop details and access token from the ShopifyConfigService
            $shop = $this->shopifyConfigService->getShop();
            $token = $this->shopifyConfigService->getToken();
            $apiVersion = $this->shopifyConfigService->getApiVersion();

            // Create a new Guzzle HTTP client
            $client = new Client();

            // Get all orders from Shopify API
            $response = $client->request(
                'GET',
                "https://{$shop}/admin/api/{$apiVersion}/orders.json?status=any",
                ['headers' => ['X-Shopify-Access-Token' => $token]]
            );

            Log::info('Shopify API request', [
                'method' => 'GET',
                'url' => "https://{$shop}/admin/api/{$apiVersion}/orders.json?status=any",
                'headers' => ['X-Shopify-Access-Token' => '******'],
            ]);

            $responseData = json_decode($response->getBody(), true);

            // Check if orders exist
            if (!isset($responseData['orders'])) {
                return redirect()->back()->with('info', 'No orders found in Shopify.');
            }

            $shopifyOrders = $responseData['orders'];
            $fulfilledOrdersCount = 0;
            $fulfilledOrders = [];

            foreach ($shopifyOrders as $shopifyOrder) {
                // Extract the basket_id from the note attribute
                $basketId = $shopifyOrder['note'] ?? null;

                // If the note was not found or does not contain a basket_id, skip this order
                if (!$basketId) {
                    continue;
                }

                // Find the order in the database by basket_id
                $order = Order::where('basket_id', $basketId)->first();

                // If the order was not found, skip this order
                if (!$order) {
                    continue;
                }

                // Check if the Shopify order is fulfilled
                if ($shopifyOrder['fulfillment_status'] === 'fulfilled') {
                    Log::debug('Order is fulfilled', ['order_id' => $shopifyOrder['id']]);
                    // If the order has been fulfilled, update it as fulfilled in the database
                    $wasAlreadyFulfilled = $order->fulfilled;
                    $order->fulfilled = true;

                    // If the order has a fulfillment, create a new fulfillment in the database
                    if (isset($shopifyOrder['fulfillments']) && count($shopifyOrder['fulfillments']) > 0) {
                        foreach ($shopifyOrder['fulfillments'] as $shopifyFulfillment) {
                            $fulfillment = Fulfillment::updateOrCreate(
                                ['shopify_fulfillment_id' => $shopifyFulfillment['id']],
                                [
                                    'order_id' => $order->id,
                                    'tracking_number' => $shopifyFulfillment['tracking_number'] ?? null,
                                    'tracking_url' => $shopifyFulfillment['tracking_url'] ?? null,
                                    'carrier_name' => $shopifyFulfillment['tracking_company'] ?? null,
                                    'carrier_code' => $shopifyFulfillment['tracking_company'] ?? null,
                                ]
                            );

                            // Dispatch the fulfillment
                        // Dispatch the fulfillment
                        if ($fulfillment->dispatched_at === null) {
                            Log::debug('About to dispatch fulfillment', ['fulfillment_id' => $fulfillment->id]);

                            // Update the dispatched_at field before sending the dispatch request
                            $fulfillment->dispatched = true;
                            $fulfillment->dispatched_at = now();
                            $fulfillment->save();
                            Log::debug('Fulfillment marked as dispatched', ['fulfillment' => $fulfillment]);

                            // Prepare the dispatch request data
                            $url = config('app.getwaterfit.api_url') . "suppliers/api/1.0/dispatchOrder/{$order->basket_id}";
                            $order->load('productCodes');

                            // Prepare the product codes data
                            $productCodes = $order->productCodes->map(function ($productCode) {
                                return [
                                    "code" => $productCode->code,
                                    "quantity" => $productCode->quantity,
                                    "basket_item_id" => $productCode->basket_item_id
                                ];
                            });

                            // Prepare the data
                            $data = [
                                "order_reference" => $order->order_reference,
                                "despatch_number" => strval($fulfillment->tracking_number), // convert to string
                                "product_codes" => $productCodes,
                                "quantity" => $order->productCodes->sum('quantity'),
                                "despatch_date" => optional($fulfillment->dispatched_at)->format('Y-m-d'),
                                "carrier_code" => $fulfillment->carrier_code,
                                "carrier_name" => $fulfillment->carrier_name,
                                "tracking_number" => $fulfillment->tracking_number,
                                "ship-method" => $order->fulfilled ? 'fulfilled' : 'unfulfilled'
                            ];

                            Log::debug('Dispatch data prepared', ['url' => $url, 'data' => $data]);

                            // Send the PUT request
                            try {
                                Log::debug('Sending dispatch request...');
                                $response = $client->request('PUT', $url, [
                                    'headers' => ['Authorization' => 'Bearer ' . config('app.getwaterfit.api_token')],
                                    'json' => $data
                                ]);
                                Log::debug('Dispatch request sent');

                                $responseBody = json_decode($response->getBody(), true);
                                Log::debug('Dispatch response received', ['response' => $responseBody]);

                                if ($response->getStatusCode() != 200 || $responseBody['message'] != 'success') {
                                    // If the dispatch request was not successful, reset the dispatched and dispatched_at fields
                                    $fulfillment->dispatched = false;
                                    $fulfillment->dispatched_at = null;
                                    $fulfillment->save();
                                    Log::debug('Fulfillment marked as not dispatched', ['fulfillment' => $fulfillment]);
                                }
                            } catch (\GuzzleHttp\Exception\GuzzleException $e) {
                                Log::error('Dispatch request failed', ['error' => $e->getMessage(), 'url' => $url, 'data' => $data]);
                            } catch (\Exception $e) {
                                Log::error('An unexpected error occurred during dispatch', ['error' => $e->getMessage(), 'url' => $url, 'data' => $data]);
                            }
                        }

                        }
                    }

                    // Update the shopify_order_id in the database
                    $order->shopify_order_id = $shopifyOrder['id'];
                    // Update the shopify_order_number in the database
                    $order->shopify_order_number = $shopifyOrder['name'];
                    // Save the order at this point to mark it as fulfilled
                    $order->save();

                    Log::info('Order after Marked as Fulfilled:', ['order' => $order]);

                    if (!$wasAlreadyFulfilled) {
                        $fulfilledOrders[] = $order;  // Add the order to the array
                        $fulfilledOrdersCount++;
                    }
                }
            }

            // Return a response
            if ($fulfilledOrdersCount > 0) {
                \Mail::to(config('app.admin_email'))->send(new FulfillmentOrdersMail($fulfilledOrders));
                return redirect()->back()->with('success', $fulfilledOrdersCount . ' orders updated successfully.');
            } else {
                return redirect()->back()->with('info', 'No orders to update.');
            }

        } catch (\Exception $e) {
            // Handle API errors here
            return redirect()->back()->with('error', 'Failed to sync orders due to a server error.');
        }
    }
}
