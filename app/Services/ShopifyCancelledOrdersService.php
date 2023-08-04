<?php
/**
 * @Author: Bernard Hanna
 * @Date:   2023-05-24 12:35:17
 * @Last Modified by:   Bernard Hanna
 * @Last Modified time: 2023-05-24 13:00:41
 */
namespace App\Services;

use App\Models\Order;
use App\Services\ShopifyConfigService;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class ShopifyCancelledOrdersService
{
    private $shopifyConfigService;

    public function __construct(ShopifyConfigService $shopifyConfigService)
    {
        $this->shopifyConfigService = $shopifyConfigService;
    }

    public function syncCancelledOrders()
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
            "https://{$shop}/admin/api/{$apiVersion}/orders.json?status=any&fields=id,name,note,fulfillment_status,cancelled_at,cancel_reason",
            ['headers' => ['X-Shopify-Access-Token' => $token]]
        );

        $responseData = json_decode($response->getBody(), true);

        // Check if orders exist
        if (!isset($responseData['orders'])) {
            return redirect()->back()->with('info', 'No orders found in Shopify.');
        }

        $shopifyOrders = $responseData['orders'];
        $cancelledOrdersCount = 0;

        Log::debug('Start syncCancelledOrders method');

        foreach ($shopifyOrders as $shopifyOrder) {
            // Extract the basket_id from the note attribute
            $basketId = $shopifyOrder['note'] ?? null;

            // If the note was not found or does not contain a basket_id, skip this order
            if (!$basketId) {
                continue;
            }

            // Find the order in the database by basket_id
            $order = Order::where('basket_id', $basketId)->first();

            // If the order was not found or has already been dispatched, skip this order
            if (!$order || $order->dispatched) {
                continue;
            }

            // Check if the Shopify order is cancelled
            if (($shopifyOrder['fulfillment_status'] === null || $shopifyOrder['fulfillment_status'] === 'restocked') && isset($shopifyOrder['cancelled_at']) && $shopifyOrder['cancelled_at'] != null) {
                // If the order has been cancelled, update it as cancelled in the database
                $order->cancelled_at = date('Y-m-d H:i:s', strtotime($shopifyOrder['cancelled_at']));
                // Update the cancel reason in the database
                $order->cancel_reason = $shopifyOrder['cancel_reason'] ?? null;
                // Save the order at this point to mark it as cancelled
                try {
                    $order->save();
                    $cancelledOrdersCount++;

                    // Prepare the cancel reason data
                    $cancelReason = $shopifyOrder['cancel_reason'] ?? null;

                    // If a cancel reason is available, send it to the external API
                    if ($cancelReason) {
                        // Prepare the cancel request data
                        $url = config('app.getwaterfit.api_url') . "suppliers/api/1.0/cancelOrder/{$order->basket_id}";
                        $cancelData = [
                            'reason' => $cancelReason
                        ];

                        // Send the PUT request to cancel the order with the reason
                        try {
                            $response = $client->request('PUT', $url, [
                                'headers' => ['Authorization' => 'Bearer ' . config('app.getwaterfit.api_token')],
                                'json' => $cancelData
                            ]);
                            $responseBody = json_decode($response->getBody(), true);
                            Log::debug('Order cancellation response', ['response' => $responseBody]);
                             // Update the cancel reason in the database
                        $order->cancel_reason = $cancelReason;
                        $order->save();
                    } catch (\GuzzleHttp\Exception\GuzzleException $e) {
                        Log::error('Order cancellation request failed', ['error' => $e->getMessage(), 'url' => $url, 'data' => $cancelData]);
                    }
                }
            } catch (\Exception $e) {
                Log::error('Error saving order:', ['error' => $e->getMessage()]);
            }
        }
    }

            // Return a response
            if ($cancelledOrdersCount > 0) {
                return redirect()->back()->with('success', $cancelledOrdersCount . ' orders updated successfully.');
            } else {
                return redirect()->back()->with('info', 'No orders to update.');
            }
        } catch (\Exception $e) {
            // Handle API errors here
            return redirect()->back()->with('error', 'Failed to sync orders due to a server error.');
        }
    }
}