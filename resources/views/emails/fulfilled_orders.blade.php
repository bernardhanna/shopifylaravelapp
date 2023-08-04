<?php
/**
 * @Author: Bernard Hanna
 * @Date:   2023-05-15 15:25:13
 * @Last Modified by:   Bernard Hanna
 * @Last Modified time: 2023-07-07 16:37:22
 */
?>
<h1>Fulfillment Orders</h1>
<p>Below are the details of the fulfilled orders:</p>
<table>
    <tr>
        <th>Basket ID:</th>
        <th>Tracking number:</th>
        <th>Tracking URL:</th>
        <th>Shopify order ID:</th>
        <th>Shopify order number:</th>
        <th>First name</th>
        <th>Surname</th>
    </tr>
    @foreach ($fulfilledOrders as $order)
        @foreach ($order->fulfillments as $fulfillment)
            <tr>
                <td>{{ $order->basket_id }}</td>
                <td>{{ $fulfillment->tracking_number }}</td>
                <td>{{ $fulfillment->tracking_url }}</td>
                <td>{{ $order->shopify_order_id }}</td>
                <td>{{ $order->shopify_order_number }}</td>
                <td>{{ $order->customer_forename }}</td>
                <td>{{ $order->customer_surname }}</td>
            </tr>
        @endforeach
    @endforeach
</table>