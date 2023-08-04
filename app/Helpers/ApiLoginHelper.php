<?php
/**
 * @Author: Bernard Hanna
 * @Date:   2023-05-09 14:43:49
 * @Last Modified by:   Bernard Hanna
 * @Last Modified time: 2023-05-16 11:36:18
 */


namespace App\Helpers;

use Illuminate\Support\Facades\Http;

class ApiLoginHelper
{
    public static function fetchPendingOrders()
    {
        $loginResponse = Http::post(config('app.getwaterfit.api_url') . 'suppliers/account/login', [
            'email' => config('app.getwaterfit.api_email'),
            'password' => config('app.getwaterfit.api_password'),
        ]);

        if (!isset($loginData['access_token'])) {
            return [
                'success' => false,
                'message' => 'Access token not found in the login response.',
            ];
        }

        $accessToken = $loginData['access_token'];

        $ordersResponse = Http::withHeaders([
            'Authorization' => 'Bearer ' . $accessToken,
        ])->get(config('app.getwaterfit.api_url') . 'suppliers/api/1.0/getPendingOrders');

        $data = json_decode($ordersResponse->body(), true);

        return [
            'success' => true,
            'data' => $data,
        ];
    }
}
