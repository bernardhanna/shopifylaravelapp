<?php
/**
 * @Author: Bernard Hanna
 * @Date:   2023-04-07 10:12:53
 * @Last Modified by:   Bernard Hanna
 * @Last Modified time: 2023-05-11 19:35:20
 */
use Illuminate\Support\Facades\Http;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Helpers\ApiLoginHelper;
use App\Order;
use App\ProductCode;
use GuzzleHttp\Client;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // Create a Guzzle HTTP client
        $client = new Client();
        // Fetch the pending orders from the API
        $loginResponse = Http::post('https://api.dev.getwaterfit.co.uk/suppliers/account/login', [
            'email' => 'straights@savewatersavemoney.co.uk',
            'password' => 'walrus pear pencil',
        ]);

        $loginData = json_decode($loginResponse->body(), true);

        if (!isset($loginData['access_token'])) {
            return [
                'success' => false,
                'message' => 'Access token not found in the login response.',
            ];
        }

        $accessToken = $loginData['access_token'];

        $ordersResponse = Http::withHeaders([
            'Authorization' => 'Bearer ' . $accessToken,
        ])->get('https://api.dev.getwaterfit.co.uk/suppliers/api/1.0/getPendingOrders');

        $ordersData = json_decode($ordersResponse->body(), true);

        foreach ($ordersData as $item) {
            $existing_order = DB::table('orders')->where('order_reference', $item['order_reference'])->first();
            if ($existing_order) {
                echo "Order with reference " . $item['order_reference'] . " already exists\n";
                continue;
            }

            $order_id = DB::table('orders')->insertGetId([
                'basket_id' => $item['basket_id'],
                'order_reference' => $item['order_reference'],
                'customer_title' => $item['customer']['title'],
                'customer_forename' => $item['customer']['forename'],
                'customer_surname' => $item['customer']['surname'],
                'customer_house_name_number' => $item['customer']['address']['house_name_number'],
                'customer_line_1' => $item['customer']['address']['line_1'],
                'customer_line_2' => $item['customer']['address']['line_2'],
                'customer_line_3' => $item['customer']['address']['line_3'],
                'customer_county' => $item['customer']['address']['county'],
                'customer_postcode' => $item['customer']['address']['postcode'],
                'customer_telephone' => $item['customer']['telephone'],
                'customer_email' => $item['customer']['email'],
                'order_line' => $item['order_line']
            ]);
            // Insert the product codes for this order
            foreach ($item['product_codes'] as $product) {
                DB::table('product_codes')->insert([
                    'basket_id' => $item['basket_id'],
                    'basket_item_id' => $product['basket_item_id'],
                    'code' => $product['code'],
                    'quantity' => $product['quantity'],
                    'stockist' => $product['stockist'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
