<?php

/**
 * @Author: Bernard Hanna
 * @Date:   2023-06-02 15:14:54
 * @Last Modified by:   Bernard Hanna
 * @Last Modified time: 2023-06-21 13:09:27
 */

namespace App\Services;

use App\Models\Order;
use App\Models\ProductCode;
use App\Models\Product;
use App\Models\Variant;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class CreateOrderService
{
    public function sendOrdersToShopify()
    {
        // Retrieve orders from the database
        $orders = Order::where('sent_to_shopify', false)->orderBy('order_reference', 'asc')->get();
        // Initialize a variable to track if any orders were sent
        $ordersSent = false;
        // Log the number of orders
        Log::info('Number of orders: ' . count($orders));
        // Loop through each order
        foreach ($orders as $order) {
            $existingOrder = Order::where('order_reference', $order->order_reference)->where('sent_to_shopify', true)->first();
            if ($existingOrder) {
                // An order with the same order_reference has already been sent
                // Mark the current order as rejected
                $order->rejected = true;
                $order->save();
                Log::info('Order with order_reference ' . $order->order_reference . ' already sent. Marked as rejected.');
                continue; // Move to the next order
            }
            // Initialize $lineItems for this order
            $lineItems = [];
            $allLineItemsValid = true;

            // Define metafields
            $order_reference = [
                'namespace' => 'global',
                'key' => 'order_reference',
                'value' => $order->order_reference,
                'type' => 'string'
            ];

            $telephone_number = [
                'namespace' => 'global',
                'key' => 'telephone',
                'value' => $order->customer_telephone,
                'type' => 'string'
            ];

            $order_line = [
                'namespace' => 'global',
                'key' => 'ship_method',
                'value' => $order->order_line,
                'type' => 'string'
            ];

            // Load the line items for this order
            $order->load('productCodes');

            // Group the product codes by code
            $productCodesGrouped = $order->productCodes->groupBy('code');

            // Loop through each product code group
            foreach ($productCodesGrouped as $code => $productCodes) {
                $productCode = ProductCode::where('code', $code)->first();

                if (!$productCode) {
                    // Handle the case where a matching product code was not found
                    // For example, you can log an error or throw an exception
                    Log::error('Product code not found: ' . $code);
                    $allLineItemsValid = false;
                    continue; // Move to the next product code group
                }

                $variant = Variant::where('sku', $code)->first();

                if (!$variant) {
                    // Handle the case where a matching variant was not found
                    // For example, you can log an error or throw an exception
                    Log::error('Variant not found for SKU: ' . $code);
                    $allLineItemsValid = false;
                    continue; // Move to the next product code group
                }

                // Log the variant being processed
                Log::info('Processing variant ' . $variant->id . ', SKU: ' . $variant->sku);

                $product = $variant->product;

                if (!$product) {
                    // Handle the case where a matching product was not found
                    // For example, you can log an error or throw an exception
                    Log::error('Product not found for variant: ' . $variant->id);
                    $allLineItemsValid = false;
                    continue; // Move to the next product code group
                }

                // Check if the product has an active status on Shopify
                if (!isset($product->status) || $product->status !== 'active') {
                    $errorMessage = 'Order number ' . $order->basket_id . ' was not sent because the product with SKU: ' . $productCode->code . ' does not have an active status on Shopify.';

                    // Prepare the email data
                    $emailData = [
                        'orderNumber' => $order->basket_id,
                        'lineItems' => $productCodes->toArray(),
                    ];

                    // Send the email notification
                    \Mail::to(config('app.admin_email'))->send(new \App\Mail\OrderErrorInactiveProducts($emailData['orderNumber'], $emailData['lineItems']));

                    $allLineItemsValid = false;
                    continue; // Move to the next product code group
                }

                // Check if the variant is active on Shopify
                if (!$this->isVariantActiveOnShopify($variant)) {
                    // Set the flag to false if any line item variant is not active on Shopify
                    $allLineItemsValid = false;
                    continue; // Move to the next product code group
                }

                // Retrieve the title property for the line item
                $lineItemTitle = $variant->product->title;

                // Calculate the total quantity for the product code group
                $totalQuantity = $productCodes->sum('quantity');

                // Add a new line item to the order
                $lineItems[] = [
                    'variant_id' => $variant->id,
                    'inventory_item_id' => $variant->inventory_item_id,
                    'product_id' => $variant->product->id,
                    'variant_title' => '(Basket Item ID: ' . $productCodes->pluck('basket_item_id')->implode(', ') . ')',
                    'title' => $lineItemTitle,
                    'price' => 0.00, // Replace with the actual price
                    'quantity' => $totalQuantity,
                    'vendor' => $productCode->stockist,
                    'sku' => $productCode->code,
                    'properties' => [
                        [
                            'name' => 'Channel ',
                            'value' => $order->order_line
                        ]
                    ]
                ];
            }

            // Check if all line items are valid
            if (!$allLineItemsValid) {
                $errorMessage = 'Order number ' . $order->basket_id . ' was not sent because of invalid line items.';

                // Prepare the email data
                $emailData = [
                    'order_number' => $order->basket_id,
                    'line_items' => $order->productCodes->toArray(),
                    'error_message' => $errorMessage,
                ];

                // Send the email notification
                \Mail::to(config('app.admin_email'))->send(new \App\Mail\OrderErrorMail($emailData));

                continue; // Skip sending the order and move to the next one
            }

            $metafields = [
                $order_reference,
                $telephone_number,
                $order_line
            ];

            $password = config('app.shopify.access_token');
            $product_codes_metafield_id = null; // Add this line to store the product codes metafield ID
            foreach ($metafields as $metafield_data) {
                $url = 'https://' . config('app.shopify.domain') . '/admin/api/' . config('app.shopify.api_version') . '/metafields.json';
                $headers = [
                    'Content-Type' => 'application/json',
                    'X-Shopify-Access-Token' => $password
                ];

                $response = Http::withHeaders($headers)->post($url, ['metafield' => $metafield_data]);
            }

            // Create the order in Shopify
            Log::info("Line items for order {$order->basket_id}: " . json_encode($lineItems));

            // Prepare the data for the order
            $order_data = [
                'order' => [
                    'note' => $order->basket_id,
                    'name' => $order->order_reference,
                    'order_number' => $order->basket_id,
                    'tags' => $order->order_line,
                    'inventory_behaviour' => 'decrement_ignore',  // or 'decrement_obeying_policy'
                    'financial_status' => 'paid', // This may need to change and is just an example, this is a required field
                    'fulfillment_status' => 'unfulfilled', // This may need to change and is just an example, this is a required field
                    'customer' => [
                        'first_name' => $order->customer_forename,
                        'last_name' => $order->customer_surname,
                        'email' => $order->customer_email,
                    ],
                    'billing_address' => [
                        'first_name' => $order->customer_forename,
                        'last_name' => $order->customer_surname,
                        'address1' => !empty(trim($order->customer_house_name_number . $order->customer_line_1)) ? $order->customer_house_name_number . ', ' . $order->customer_line_1 : 'N/A',
                        'address2' => !empty($order->customer_line_2) ? $order->customer_line_2 : 'N/A',
                        'city' => !empty($order->customer_line_3) ? $order->customer_line_3 : 'N/A',
                        'phone' => ltrim($order->customer_telephone, '+'),
                        'province' => 'CA',
                        'country' => 'GB',
                        'zip' => !empty($order->customer_postcode) ? $order->customer_postcode : 'N/A',
                    ],
                    'shipping_address' => [
                        'first_name' => $order->customer_forename,
                        'last_name' => $order->customer_surname,
                        'address1' => !empty(trim($order->customer_house_name_number . $order->customer_line_1)) ? $order->customer_house_name_number . ', ' . $order->customer_line_1 : 'N/A',
                        'address2' => !empty($order->customer_line_2) ? $order->customer_line_2 : 'N/A',
                        'city' => !empty($order->customer_line_3) ? $order->customer_line_3 : 'N/A',
                        'phone' => ltrim($order->customer_telephone, '+'),
                        'province' => 'CA',
                        'country' => 'GB',
                        'zip' => !empty($order->customer_postcode) ? $order->customer_postcode : 'N/A',
                    ],
                    'line_items' => $lineItems,
                    'metafields' => [
                        [
                            'namespace' => 'global',
                            'key' => 'order_reference',
                            'value' => $order->order_reference,
                            'type' => 'string'
                        ],
                        [
                            'namespace' => 'global',
                            'key' => 'telephone',
                            'value' => $order->customer_telephone,
                            'type' => 'string'
                        ],
                        [
                            'namespace' => 'global',
                            'key' => 'order_line',
                            'value' => $order->order_line,
                            'type' => 'string'
                        ],
                    ],
                ],
            ];
            $endpoint = 'https://' . config('app.shopify.domain') . '/admin/api/' . config('app.shopify.api_version') . '/orders.json';

            // For the order creation request
            Log::info('Sending order request: ' . json_encode($order_data));
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'X-Shopify-Access-Token' => $password,
            ])->post($endpoint, $order_data);

            Log::info('Order response: ' . $response->body());

            // Check for HTTP errors
            if ($response->failed()) {
                $error_msg = 'HTTP error: ' . $response->status() . ' ' . $response->body();
                // Log the error and handle it gracefully
                Log::error($error_msg);

                $errorMessage = 'Order number ' . $order->basket_id . ' was not sent due to an error.';

                // Prepare the email data
                $emailData = [
                    'order_number' => $order->basket_id,
                    'line_items' => $order->productCodes->toArray(),
                    'error_message' => $errorMessage,
                ];

                // Send the email notification
                \Mail::to(config('app.admin_email'))->send(new \App\Mail\OrderErrorMail($emailData));

                continue; // Skip sending the order and move to the next one
            }

            // Set the order as sent to Shopify
            $order->sent_to_shopify = true;
            $order->save();
            Log::info('Order has been sent to Shopify successfully: Order ID ' . $order->basket_id);

            // Check if any line items were added
            if (count($lineItems) > 0) {
                $ordersSent = true;
            }
        }

        // Set the session message
        if ($ordersSent) {
            session()->flash('success', 'Orders have been sent to Shopify');
        } else {
            session()->flash('info', 'No orders were sent');
        }

        if ($ordersSent) {
            \Mail::to(config('app.admin_email'))->send(new \App\Mail\NewOrderMail($orders));
            session()->flash('success', 'Orders have been sent to Shopify');
        } else {
            \Mail::to(config('app.admin_email'))->send(new \App\Mail\NewOrderMail([]));
            session()->flash('info', 'No orders were sent');
        }

        return redirect()->back();
    }

    private function isVariantActiveOnShopify(Variant $variant): bool
    {
        $password = config('app.shopify.access_token');
        $endpoint = 'https://' . config('app.shopify.domain') . '/admin/api/' . config('app.shopify.api_version') . '/variants/' . $variant->id . '.json';

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'X-Shopify-Access-Token' => $password,
        ])->get($endpoint);

        if ($response->failed()) {
            Log::error('Failed to retrieve variant status from Shopify: ' . $response->status() . ' ' . $response->body());
            return false; // Assume the variant is not active
        }

        $variantData = $response->json();

        // Check the variant status
        if (isset($variantData['variant']['status']) && $variantData['variant']['status'] !== 'active') {
            Log::info('Skipping variant ' . $variant->id . ' because its status is not active on Shopify.');
            return false;
        }

        // Check if the variant's product is also active
        if (isset($variantData['variant']['product_id'])) {
            $product = Product::find($variantData['variant']['product_id']);
            if ($product && !$this->isProductActiveOnShopify($product)) {
                Log::info('Skipping variant ' . $variant->id . ' because its associated product is not active on Shopify.');
                return false;
            }
        }

        return true;
    }

    private function isProductActiveOnShopify(Product $product): bool
    {
        $password = config('app.shopify.access_token');
        $endpoint = 'https://' . config('app.shopify.domain') . '/admin/api/' . config('app.shopify.api_version') . '/products/' . $product->id . '.json';

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'X-Shopify-Access-Token' => $password,
        ])->get($endpoint);

        if ($response->failed()) {
            Log::error('Failed to retrieve product status from Shopify: ' . $response->status() . ' ' . $response->body());
            return false; // Assume the product is not active
        }

        $productData = $response->json();

        // Check the product status
        if (isset($productData['product']['status']) && $productData['product']['status'] !== 'active') {
            Log::info('Skipping product ' . $product->id . ' because its status is not active on Shopify.');
            return false;
        }

        return true;
    }
}
