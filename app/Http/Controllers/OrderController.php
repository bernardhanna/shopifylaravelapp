<?php
/**
 * @Author: Bernard Hanna
 * @Date:   2023-05-03 17:56:43
 * @Last Modified by:   Bernard Hanna
 * @Last Modified time: 2023-05-24 12:15:41
 */
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\OrderSyncService;

use Illuminate\Support\Facades\Http;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client;
use App\Models\Order;
use App\Mail\SyncOrdersMail;

class OrderController extends Controller
{
    public function __construct(OrderSyncService $orderSyncService)
    {
        $this->orderSyncService = $orderSyncService;
    }

    public function syncOrders()
    {
        return $this->orderSyncService->syncOrders();
    }

    public function showOrders()
    {
        return $this->orderSyncService->showOrders();
    }

 }
