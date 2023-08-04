<?php
/**
 * @Author: Bernard Hanna
 * @Date:   2023-06-02 08:57:03
 * @Last Modified by:   Bernard Hanna
 * @Last Modified time: 2023-06-02 09:02:54
 */


return [

    'credentials' => [

        /*
         * The API access token from the private app.
         */
        'access_token' => env('SHOPIFY_ACCESS_TOKEN', ''),

        /*
         * The shopify domain for your shop.
         */
        'domain' => env('SHOPIFY_DOMAIN', ''),

        /*
         * Location ID.
         */
        'location_id' => env('SHOPIFY_LOCATION_ID', ''),

        /*
         * The shopify api version.
         */
        'api_version' => env('SHOPIFY_API_VERSION', '2021-01'),

    ],

    'webhooks' => [

        /*
         * The webhook secret provider to use.
         */
        'secret_provider' => \Signifly\Shopify\Webhooks\ConfigSecretProvider::class,

        /*
         * The shopify webhook secret.
         */
        'secret' => env('SHOPIFY_WEBHOOK_SECRET'),

    ],

    'exceptions' => [

        /*
         * Whether to include the validation errors in the exception message.
         */
        'include_validation_errors' => false,

    ],
];
