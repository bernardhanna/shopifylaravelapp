<?php
/**
 * @Author: Bernard Hanna
 * @Date:   2023-06-16 10:28:02
 * @Last Modified by:   Bernard Hanna
 * @Last Modified time: 2023-06-21 12:46:03
 */

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client;

class OrderSyncService
{
    public function syncOrders()
    {
        // Create a Guzzle HTTP client
        $client = new Client();

        $loginResponse = Http::post(config('app.getwaterfit.api_url') . 'suppliers/account/login', [
            'email' => config('app.getwaterfit.api_email'),
            'password' => config('app.getwaterfit.api_password'),
        ]);

        $loginData = json_decode($loginResponse->body(), true);

        // Log the login response
        if (!isset($loginData['token'])) {
            Log::error('Login error: Access token not found in the login response.');
            return [
                'success' => false,
                'message' => 'Access token not found in the login response.',
            ];
        } else {
            Log::info('Login success: Access token retrieved successfully.');
        }

        $accessToken = $loginData['token'];

        $retryCount = 0;
        $maxRetries = 3;
        $ordersResponse = null;

        while ($retryCount < $maxRetries) {
            try {
                $ordersResponse = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $accessToken,
                ])->timeout(60)->get(config('app.getwaterfit.api_url') . 'suppliers/api/1.0/getPendingOrders');

                // Check if the request was successful
                if ($ordersResponse->successful()) {
                    break; // Exit the retry loop if the request succeeds
                } else {
                    Log::error('Failed to fetch pending orders. Response: ' . json_encode($ordersResponse->body()));
                }
            } catch (\Exception $e) {
                Log::error('Failed to fetch pending orders. Exception: ' . $e->getMessage());
            }

            $retryCount++;
        }

        if (!$ordersResponse || !$ordersResponse->successful()) {
            return redirect()->back()->with('error', 'SWSM API timed out. Please try again.');
        }

        $data = json_decode($ordersResponse->body(), true);

        // Check if $data is empty
        if (empty($data)) {
            Log::info('Orders API response: No orders to sync to the database');
        } else {
            Log::info('Orders API response: ' . json_encode($data));
        }

        $count = 0;
        foreach ($data as $item) {
            $existing_order = Order::where('basket_id', $item['basket_id'])->first();
            if ($existing_order) {
                continue;
            }
            $order = new Order();
            $order->basket_id = $item['basket_id'] ?? '';
            $order->order_reference = $item['order_reference'] ?? '';
            $order->customer_title = $item['customer']['title'] ?? '';
            $order->customer_forename = $item['customer']['forename'] ?? '';
            $order->customer_surname = $item['customer']['surname'] ?? '';
            $order->customer_house_name_number = $item['customer']['address']['house_name_number'] ?? '';
            $order->customer_line_1 = $item['customer']['address']['line1'] ?? '';
            $order->customer_line_2 = $item['customer']['address']['line2'] ?? '';
            $order->customer_line_3 = $item['customer']['address']['line3'] ?? '';
            $order->customer_county = $item['customer']['address']['county'] ?? '';
            $order->customer_postcode = $item['customer']['address']['postcode'] ?? '';
            $order->customer_telephone = $item['customer']['telephone'] ?? '';
            $order->customer_email = $item['customer']['email'] ?? '';
            $order->order_line = $item['order_line'] ?? '';
            try {
                $order->save();
                Log::info('New order created: ' . $order->order_reference);
            } catch (\Exception $e) {
                Log::error('Failed to create new order: ' . $e->getMessage());
            }

            // Insert the product codes for this order
            foreach ($item['products'] as $product) {
                try {
                    DB::table('product_codes')->insert([
                        'basket_id' => $order->basket_id,
                        'basket_item_id' => $product['basket_item_id'] ?? '',
                        'code' => $product['code'] ?? '',
                        'quantity' => $product['quantity'] ?? 0,
                        'stockist' => $product['stockist'] ?? '',
                    ]);
                    Log::info('Product code inserted for basket_id: ' . $order->basket_id);
                } catch (\Exception $e) {
                    Log::error('Failed to insert product code: ' . $e->getMessage());
                }
            }

            // Try to make the PATCH request and handle any errors
            try {
                $acceptOrderResponse = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $accessToken,
                ])->patch(config('app.getwaterfit.api_url') . 'suppliers/api/1.0/acceptOrder/' . $order->basket_id);

                // Check if the request was successful
                if ($acceptOrderResponse->successful()) {
                    Log::info('Accept order response: ' . json_encode($acceptOrderResponse->body()));
                } else {
                    // Log the unsuccessful response for troubleshooting
                    Log::error('Failed to accept order. Response: ' . json_encode($acceptOrderResponse->body()));
                }
            } catch (\Exception $e) {
                // Log the exception for troubleshooting
                Log::error('Failed to accept order. Exception: ' . $e->getMessage());
            }
            $count++;
        }
        if ($count > 0) {
            return redirect()->back()->with('success', $count . ' orders synced successfully.');
        } else {
            return redirect()->back()->with('info', 'Nothing to sync.');
        }
    }

    // Pagination
    public function showOrders()
    {
        $allOrders = Order::orderBy('id', 'desc')->paginate(50);
        return view('dashboard', compact('allOrders'));  // replace 'order' with the actual name of your blade file without .blade.php
    }
}
