<?php

require_once __DIR__ . '/_shared.php';

$runtime = gkash_runtime();
$store = $runtime['store'];
$handler = $runtime['handler'];
$orderId = gkash_request_order_id();
$record = array();
if ($orderId !== '') {
    $record = $store->loadOrder($orderId);
}

$callback = isset($record['callback']) && is_array($record['callback']) ? $record['callback'] : array();

$hasInlineCallback = false;
foreach (array('poid', 'cartid', 'amount', 'currency', 'status', 'signature', 'v_cartid', 'gatewaytransid') as $field) {
    if (isset($_REQUEST[$field])) {
        $hasInlineCallback = true;
        break;
    }
}

if ($hasInlineCallback) {
    $response = $handler->handle($_REQUEST, array('allow_requery' => true));
    header('Location: ' . ($response->success ? 'success.php' : 'failed.php') . '?order_id=' . rawurlencode($response->order_id));
    exit;
}

if (!empty($callback)) {
    $target = !empty($callback['success']) ? 'success.php' : 'failed.php';
    header('Location: ' . $target . '?order_id=' . rawurlencode($orderId));
    exit;
}

$body = '<div class="card"><h1 style="margin-top:0">Waiting for GKash response</h1>';
$body .= '<p class="notice">The browser return has arrived before a callback was stored. Refresh this page after the callback completes, or enable demo mode for a local end-to-end simulation.</p>';
if ($orderId !== '') {
    $body .= '<p><strong>Order ID:</strong> ' . gkash_h($orderId) . '</p>';
}
$body .= '<p><a class="btn primary" href="../examples/simple-checkout.php">Back to checkout</a></p></div>';

gkash_render_page('GKash Return', $body);

