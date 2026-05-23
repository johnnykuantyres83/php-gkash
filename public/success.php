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
$body .= '<h1 style="margin-top:0;color:#295235">Payment successful</h1>';
$body .= '<div class="notice ok">The GKash callback was verified successfully.</div>';
$body .= gkash_render_kv_table(array(
    'Order ID' => gkash_h(isset($order['order_id']) ? $order['order_id'] : $orderId),
    'Transaction ID' => gkash_h(isset($callback['transaction_id']) ? $callback['transaction_id'] : ''),
    'Amount' => gkash_h(isset($callback['amount']) ? $callback['amount'] : (isset($order['amount']) ? $order['amount'] : '')),
    'Currency' => gkash_h(isset($callback['currency']) ? $callback['currency'] : (isset($order['currency']) ? $order['currency'] : 'MYR')),
    'Customer Name' => gkash_h(isset($order['customer_name']) ? $order['customer_name'] : ''),
    'Payment Status' => gkash_h(isset($callback['status']) ? $callback['status'] : '88'),
    'Signature Valid' => gkash_h(!empty($callback['signature_valid']) ? 'Yes' : 'No'),
));

if ((bool) gkash_config('display.debug_raw_callback', false) || isset($_GET['debug'])) {
    $body .= '<h2>Raw callback</h2>';
    $body .= gkash_render_json_pre($callback);
}

$body .= '<p style="margin-top:18px"><a class="btn primary" href="../examples/simple-checkout.php">New payment</a> <a class="btn secondary" href="failed.php?order_id=' . rawurlencode($orderId) . '">View failed page</a></p>';
$body .= '</div>';

gkash_render_page('GKash Success', $body);

