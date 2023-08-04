<?php
/**
 * @Author: Bernard Hanna
 * @Date:   2023-05-15 11:47:49
 * @Last Modified by:   Bernard Hanna
 * @Last Modified time: 2023-05-15 12:09:18
 */


namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Response;

class ShopifyConfigService
{
    private $shop;
    private $token;
    private $apiVersion;

    public function __construct()
    {
        $this->shop = config('app.shopify.domain');
        $this->token = config('app.shopify.access_token');
        $this->apiVersion = config('app.shopify.api_version');
    }

    public function getShop()
    {
        return $this->shop;
    }

    public function getToken()
    {
        return $this->token;
    }

    public function getApiVersion()
    {
        return $this->apiVersion;
    }
}
