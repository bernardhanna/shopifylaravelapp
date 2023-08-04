<?php
/**
 * @Author: Bernard Hanna
 * @Date:   2023-05-03 17:32:07
 * @Last Modified by:   Bernard Hanna
 * @Last Modified time: 2023-07-07 15:42:21
 */
?>
<div class="m-auto">
    <div class="container-fluid">
        <div class="row pt-4">
            <div class="col-2">
                <form method="POST" action="{{ route('sync-shopify-products') }}">
                    @csrf
                    <button type="submit">Sync Products</button>
                </form>
            </div>
            <div class="col-2">
                 <form method="POST" action="{{ route('orders.sync') }}">
                    @csrf
                    <button type="submit">Sync Orders</button>
                </form>
            </div>
            <div class="col-2">
                <form method="POST" action="{{ route('orders.sendOrdersToShopify') }}">
                    @csrf
                    <button type="submit">Send to shopify</button>
                </form>
            </div>
            <div class="col-2">
                <form method="POST" action="{{ route('sync-fulfillments') }}">
                    @csrf
                    <button type="submit">Fulfill Orders</button>
                </form>
            </div>
            <div class="col-2">
                <form method="POST" action="{{ route('sync-cancelled-orders') }}">
                    @csrf
                    <button type="submit">Sync Cancelled Orders</button>
                </form>
            </div>
            <div class="col-2">
                <form method="POST" action="{{ route('garbage-collection') }}">
                    @csrf
                    <button type="submit">Garbage Collection</button>
                </form>
            </div>
            <div class="col-12">
            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif
            @if(session('info'))
                <div class="alert alert-info">
                    {{ session('info') }}
                </div>
            @endif
            </div>
        </div>
        <table class="table">
            <thead>
                <tr>
                    <th>Created</th>
                    <th>Basket ID</th>
                    <th>Order Reference</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Telephone</th>
                    <th>Address</th>
                    <th>Orderline</th>
                    <th>Sent to Shopify?</th>
                    <th>Status</th>                  
                    <th>Shopify Order ID</th>
                    <th>Shopify Order Number</th>
                    <th>Cancel date</th>
                    <th>Cancel Reason </th>
                    <th>Rejected</th>
                    <th>Dispatched</th>
                    <th>Dispatched At</th>
                    <th>Tracking Number</th>
                    <th>Tracking URL</th>
                    <th>Carrier Code</th>
                    <th>Carrier Name</th>
                 </tr>
            </thead>
            <tbody>
            @foreach ($allOrders as $order)
                    <tr>
                        <!-- ... existing order data ... -->
                        <td>{{ $order->created_at }}</td>
                        <td>{{ $order->basket_id }}</td>
                        <td>{{ $order->order_reference }}</td>
                        <td>{{ $order->customer_title }} {{ $order->customer_forename }} {{ $order->customer_surname }}</td>
                        <td>{{ $order->customer_email }}</td>
                        <td>{{ $order->customer_telephone }}</td>
                        <td>{{ $order->customer_house_name_number }} {{ $order->customer_line_1 }}{{ $order->customer_line_2 }} {{ $order->customer_line_3 }}{{ $order->customer_county }}, {{ $order->customer_postcode }}{{ $order->customer_county }}</td>
                        <td>{{ $order->order_line }}</td>
                        <td>{{ $order->sent_to_shopify ? 'Yes' : 'No' }}</td>
                        <td>{{ $order->fulfilled ? 'Fulfilled' : 'Not Fulfilled' }}</td>
                        <td>{{ $order->shopify_order_id }}</td>
                        <td>{{ $order->shopify_order_number }}</td>
                        <td>{{ $order->cancelled_at }}</td>
                        <td>{{ $order->cancel_reason }}</td>
                        <td>{{ $order->rejected ? 'Rejected' : 'No' }}</td>
                        <!-- Fulfillments -->
                        <td>
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Fulfillment ID</th>
                                        <th>Tracking Number</th>
                                        <th>Tracking URL</th>
                                        <th>Carrier Name</th>
                                        <th>Carrier Code</th>
                                        <th>Dispatched At</th>
                                    </tr>
                                </thead>
                                <tbody>
                                @if($order->fulfillments)
                                    @foreach ($order->fulfillments as $fulfillment)
                                        <tr>
                                            <td>{{ $fulfillment->shopify_fulfillment_id }}</td>
                                            <td>{{ $fulfillment->tracking_number }}</td>
                                            <td>{{ $fulfillment->tracking_url }}</td>
                                            <td>{{ $fulfillment->carrier_name }}</td>
                                            <td>{{ $fulfillment->carrier_code }}</td>
                                            <td>{{ $fulfillment->dispatched_at }}</td>
                                        </tr>
                                        @endforeach
                                    @endif
                                </tbody>
                            </table>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="w-100 d-flex justify-content-center mt-5">{{ $allOrders->links('pagination::bootstrap-4') }}</div>
    </div>
</div>
