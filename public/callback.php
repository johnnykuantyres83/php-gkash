<?php

require_once __DIR__ . '/_shared.php';

$runtime = gkash_runtime();
$handler = $runtime['handler'];

$hasPayload = false;
foreach (array('poid', 'cartid', 'amount', 'currency', 'status', 'signature', 'v_cartid', 'gatewaytransid') as $field) {
    if (isset($_REQUEST[$field])) {
        $hasPayload = true;
        break;
    }
}

if (!$hasPayload) {
    http_response_code(400);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(array(
        'success' => false,
        'message' => 'Missing GKash callback payload.',
    ));
    exit;
}

$response = $handler->handle($_REQUEST, array('allow_requery' => true));

if (isset($_REQUEST['redirect']) && $_REQUEST['redirect'] === '1') {
    header('Location: return.php?order_id=' . rawurlencode($response->order_id));
    exit;
}

header('Content-Type: application/json; charset=utf-8');
echo $response->toJson();

