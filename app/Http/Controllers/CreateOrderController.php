<?php
/**
 * @Author: Bernard Hanna
 * @Date:   2023-04-20 17:06:51
 * @Last Modified by:   Bernard Hanna
 * @Last Modified time: 2023-05-24 12:22:42
 */

 namespace App\Http\Controllers;

 use Illuminate\Http\Client\Response;
 use Illuminate\Support\Facades\Http;
 use Illuminate\Support\Facades\DB;
 use Illuminate\Support\Facades\Mail;
 use Illuminate\Support\Facades\Log;
 use Exception;
 use App\Services\CreateOrderService;
 
 class CreateOrderController extends Controller
 {
     private $createOrderService;
 
     public function __construct(CreateOrderService $createOrderService)
     {
         $this->createOrderService = $createOrderService;
     }
 
     public function sendOrdersToShopify()
     {
         return $this->createOrderService->sendOrdersToShopify();
     }
 }