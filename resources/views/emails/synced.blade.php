<?php
/**
 * @Author: Bernard Hanna
 * @Date:   2023-05-15 12:45:11
 * @Last Modified by:   Bernard Hanna
 * @Last Modified time: 2023-05-15 12:46:25
 */
?>
@component('mail::message')
# Products Synced

@if(count($products) > 0)
Here are the products that got synched today:

@component('mail::table')
| Product ID | Product Title |
| --- | --- |
@foreach($products as $product)
| {{ $product['id'] }} | {{ $product['title'] }} |
@endforeach
@endcomponent

@else
There were no new products to synch today.
@endif

Thanks,<br>
{{ config('app.name') }}
@endcomponent
