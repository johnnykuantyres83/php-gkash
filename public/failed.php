<?php

require_once __DIR__ . '/_shared.php';

$runtime = gkash_runtime();
$store = $runtime['store'];
$orderId = gkash_request_order_id();
$record = $orderId !== '' ? $store->loadOrder($orderId) : array();
$order = isset($record['order']) && is_array($record['order']) ? $record['order'] : array();
$callback = isset($record['callback']) && is_array($record['callback']) ? $record['callback'] : array();

if ($orderId === '' && isset($callback['order_id'])) {
    $orderId = $callback['order_id'];
}

$body = '<div class="card">';
$body .= '<h1 style="margin-top:0;color:#6d2a1e">Payment failed</h1>';
$body .= '<div class="notice fail">The GKash payment did not complete successfully.</div>';
$body .= gkash_render_kv_table(array(
    'Status Code' => gkash_h(isset($callback['status']) ? $callback['status'] : ''),
    'Order ID' => gkash_h(isset($order['order_id']) ? $order['order_id'] : $orderId),
    'Transaction ID' => gkash_h(isset($callback['transaction_id']) ? $callback['transaction_id'] : ''),
    'Amount' => gkash_h(isset($callback['amount']) ? $callback['amount'] : (isset($order['amount']) ? $order['amount'] : '')),
    'Currency' => gkash_h(isset($callback['currency']) ? $callback['currency'] : (isset($order['currency']) ? $order['currency'] : 'MYR')),
    'Error' => gkash_h(isset($callback['message']) ? $callback['message'] : 'Unknown error'),
));

if ((bool) gkash_config('display.debug_raw_callback', false) || isset($_GET['debug'])) {
    $body .= '<h2>Raw callback</h2>';
    $body .= gkash_render_json_pre($callback);
}

$body .= '<p style="margin-top:18px"><a class="btn primary" href="../examples/simple-checkout.php">Try again</a></p>';
$body .= '</div>';

gkash_render_page('GKash Failed', $body);

