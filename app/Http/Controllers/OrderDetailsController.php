<?php
/**
 * @Author: Bernard Hanna
 * @Date:   2023-05-08 16:51:14
 * @Last Modified by:   Bernard Hanna
 * @Last Modified time: 2023-05-08 16:55:17
 */


namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Signifly\Shopify\Shopify;

class OrderDetailsController extends Controller
{
    public function show(Shopify $shopify, $order_id)
    {
        try {
            $order = $shopify->get("orders/{$order_id}")->throw();
            $metafields = $shopify->get("orders/{$order_id}/metafields")->throw();
        } catch (\Exception $e) {
            // Log the error or display an error message to the user
            return response()->view('error', ['message' => $e->getMessage()]);
        }

        dd($order, $metafields); // Debugging statement

        return view('order-details', compact('order', 'metafields'));
    }

}
