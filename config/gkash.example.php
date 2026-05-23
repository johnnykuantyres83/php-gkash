<?php

return array(
    'environment' => 'sandbox',
    'demo_mode' => true,
    'version' => '1.0',
    'currency' => 'MYR',
    'cid' => 'YOUR_CID_HERE',
    'signature_key' => 'YOUR_SIGNATURE_KEY_HERE',
    'checkout_endpoint' => 'https://api.gkash.my/api/PaymentForm.aspx',
    'query_endpoint' => 'https://api.gkash.my/api/payment/query',
    'return_url' => 'https://your-domain.example/public/return.php',
    'callback_url' => 'https://your-domain.example/public/callback.php',
    'base_url' => 'https://api.gkash.my',
    'log_path' => dirname(__DIR__) . '/logs',
    'cache_path' => dirname(__DIR__) . '/cache',
    'payment_method' => 'card',
    'preselection' => array(
        'card' => 'ECOMM',
        'ewallet' => 'EWALLET',
    ),
    'http' => array(
        'timeout' => 30,
        'connect_timeout' => 10,
        'user_agent' => 'GKash-PHP/1.0',
    ),
    'security' => array(
        'verify_amount' => true,
        'verify_currency' => true,
        'replay_ttl' => 86400,
    ),
    'display' => array(
        'debug_raw_callback' => false,
    ),
);
