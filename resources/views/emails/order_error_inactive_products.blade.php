<?php
/**
 * @Author: Bernard Hanna
 * @Date:   2023-06-06 14:49:24
 * @Last Modified by:   Bernard Hanna
 * @Last Modified time: 2023-06-07 09:09:32
 */
?>
<!DOCTYPE html>
<html>
<head>
    <title>Order Error: Product does not have an Active Status</title>
</head>
<body>
    <h1>Order Error: Product does not have an Active Status</h1>
    <p>
        Order number {{ $order_number }} was not sent because we could not find active status products on the Shopify Store.
        Please review the following line items and ensure they havce the Active status on your Shopify Store:
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
        When this issue is corrected on your Shopify store, we will attempt to send the order again.
    </p>
</body>
</html>
