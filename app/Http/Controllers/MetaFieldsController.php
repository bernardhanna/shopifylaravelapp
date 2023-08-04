<?php
/**
 * @Author: Bernard Hanna
 * @Date:   2023-04-20 16:52:59
 * @Last Modified by:   Bernard Hanna
 * @Last Modified time: 2023-07-28 14:17:53
 */
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

$order_reference = [
    'namespace' => 'global',
    'key' => 'order_reference',
    'value' => $order['order_reference'],
    'type' => 'string'
];

$telephone_number = [
    'namespace' => 'global',
    'key' => 'telephone',
    'value' => $order['customer']['telephone'],
    'type' => 'string'
];

$despatch_number = [
    'namespace' => 'global',
    'key' => 'despatch_number',
    'value' => $order['despatch_date'],
    'type' => 'string'
];

$despatch_date = [
    'namespace' => 'global',
    'key' => 'despatch_date',
    'value' => $order['despatch_date'],
    'type' => 'string'
];

$carrier_code = [
    'namespace' => 'global',
    'key' => 'carrier_code',
    'value' => $order['carrier_code'],
    'type' => 'string'
];

$carrier_name = [
    'namespace' => 'global',
    'key' => 'carrier_name',
    'value' => $order['carrier_name'],
    'type' => 'string'
];

$tracking_number = [
    'namespace' => 'global',
    'key' => 'tracking_number',
    'value' => $order['ship_method'],
    'type' => 'string'
];

$ship_method = [
    'namespace' => 'global',
    'key' => 'ship_method',
    'value' => $order['ship_method'],
    'type' => 'string'
];

$product_codes = [
    'namespace' => 'global',
    'key' => 'product_codes',
    'value' => json_encode($order['product_codes']),
    'type' => 'json'
];

$metafields = [
    $order_reference,
    $telephone_number,
    $despatch_number,
    $despatch_date,
    $carrier_code,
    $tracking_number,
    $ship_method,
    $carrier_name,
    $product_codes
];

$password = config('app.shopify.access_token');

foreach ($metafields as $metafield_data) {
    $url = 'https://' . config('app.shopify.domain') . '/admin/api/' . config('app.shopify.api_version') . '/metafields.json';
    $headers = [
        "Content-Type" => "application/json",
        "X-Shopify-Access-Token" => $password
    ];
    $response = Http::withHeaders($headers)->post($url, ["metafield" => $metafield_data]);
    $responseBody = $response->json();
    // handle response
}
$endpoint = 'https://' . config('app.shopify.domain') . '/admin/api/' . config('app.shopify.api_version') . '/orders.json?status=any';
$api_key = config('app.shopify.api_key');
$password = config('app.shopify.access_token');
$headers = [
    "Content-Type" => "application/json",
    "X-Shopify-Access-Token" => $password
];
$response = Http::withHeaders($headers)->get($endpoint);
$responseBody = $response->json();
// handle response
