<?php
/**
 * @Author: Bernard Hanna
 * @Date:   2023-06-06 12:05:32
 * @Last Modified by:   Bernard Hanna
 * @Last Modified time: 2023-06-06 16:20:04
 */
?>
<!DOCTYPE html>
<html>
<head>
    <title>Order Error: Missing SKU</title>
</head>
<body>
    <h1>Order Error: Missing SKU</h1>
    <p>
        Order number <strong>{{ $order_number }}</strong> was not sent because we could not find a matching SKU.
        Please review the following line items:
    </p>

    <table>
        <thead>
            <tr>
                <th>Product Code</th>
                <th>Quantity</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($line_items as $item)
                <tr>
                    <td>{{ $item['code'] }}</td>
                    <td>{{ $item['quantity'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <p>
       This Order will be held on the system until the SKU is updated. When this issue is corrected on your Shopify store, we will attempt to send the order again. After 90 days, the order will be cancelled.
    </p>
</body>
</html>
