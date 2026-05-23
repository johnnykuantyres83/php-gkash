<?php

require_once __DIR__ . '/../public/_shared.php';

$defaults = array(
    'signature_key' => gkash_config('signature_key', ''),
    'cid' => gkash_config('cid', ''),
    'cart_id' => 'ORDER1001',
    'poid' => 'PO123456',
    'amount' => '10.00',
    'currency' => gkash_config('currency', 'MYR'),
    'status' => '88 - Transferred',
    'payment_type' => 'checkout',
);

$input = array_merge($defaults, $_POST);
$checkoutString = '';
$checkoutHash = '';
$callbackString = '';
$callbackHash = '';
$result = array();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $signatureKey = trim($input['signature_key']);
    $cid = trim($input['cid']);
    $cartId = trim($input['cart_id']);
    $poid = trim($input['poid']);
    $amount = trim($input['amount']);
    $currency = trim($input['currency']);
    $status = trim($input['status']);

    $checkoutString = GKashSignature::buildSignatureString(array(
        $signatureKey,
        $cid,
        $cartId,
        GKashSignature::amountWithoutDot($amount),
        $currency,
    ));
    $checkoutHash = GKashSignature::hashSignatureString($checkoutString);

    $callbackString = GKashSignature::buildSignatureString(array(
        $signatureKey,
        $cid,
        $poid,
        $cartId,
        GKashSignature::amountWithoutDot($amount),
        $currency,
        $status,
    ));
    $callbackHash = GKashSignature::hashSignatureString($callbackString);

    $result = array(
        'checkout_signature_valid' => GKashSignature::verifyCheckoutSignature($signatureKey, $cid, $cartId, $amount, $currency, $checkoutHash),
        'callback_signature_valid' => GKashSignature::verifyCallbackSignature($signatureKey, $cid, $poid, $cartId, $amount, $currency, $status, $callbackHash),
        'success_status' => GKashSignature::isSuccessfulStatus($status),
    );
}

$content = '';
$content .= '<div class="hero">';
$content .= '<div class="card">';
$content .= '<span class="tag">Utility</span>';
$content .= '<h1 class="title">GKash hash tester</h1>';
$content .= '<p class="sub">Use this page to verify the exact SHA512 input string and output for checkout and callback signatures before testing a payment flow.</p>';
$content .= '<form method="post" style="margin-top:18px">';
$content .= '<div class="grid">';
$fields = array(
    'signature_key' => 'Signature Key',
    'cid' => 'CID',
    'cart_id' => 'Cart ID',
    'poid' => 'POID',
    'amount' => 'Amount',
    'currency' => 'Currency',
    'status' => 'Status',
);

foreach ($fields as $name => $label) {
    $content .= '<div class="field"><label for="' . gkash_h($name) . '">' . gkash_h($label) . '</label><input id="' . gkash_h($name) . '" name="' . gkash_h($name) . '" value="' . gkash_h($input[$name]) . '"></div>';
}

$content .= '<div class="field"><label for="payment_type">Mode</label><select id="payment_type" name="payment_type">';
$content .= '<option value="checkout"' . ($input['payment_type'] === 'checkout' ? ' selected' : '') . '>Checkout signature</option>';
$content .= '<option value="callback"' . ($input['payment_type'] === 'callback' ? ' selected' : '') . '>Callback signature</option>';
$content .= '</select></div>';
$content .= '</div>';
$content .= '<div class="row" style="margin-top:18px">';
$content .= '<button class="btn primary" type="submit">Generate hashes</button>';
$content .= '<a class="btn secondary" href="../examples/simple-checkout.php">Back to checkout</a>';
$content .= '</div>';
$content .= '</form>';
$content .= '</div>';

$content .= '<div class="card">';
$content .= '<h2 style="margin-top:0">Generated values</h2>';
$content .= gkash_render_kv_table(array(
    'Checkout string' => gkash_h($checkoutString !== '' ? $checkoutString : '-'),
    'Checkout hash' => gkash_h($checkoutHash !== '' ? $checkoutHash : '-'),
    'Callback string' => gkash_h($callbackString !== '' ? $callbackString : '-'),
    'Callback hash' => gkash_h($callbackHash !== '' ? $callbackHash : '-'),
    'Checkout valid' => gkash_h(isset($result['checkout_signature_valid']) ? ($result['checkout_signature_valid'] ? 'Yes' : 'No') : '-'),
    'Callback valid' => gkash_h(isset($result['callback_signature_valid']) ? ($result['callback_signature_valid'] ? 'Yes' : 'No') : '-'),
    'Success status' => gkash_h(isset($result['success_status']) ? ($result['success_status'] ? 'Yes' : 'No') : '-'),
));
$content .= '<p class="meta" style="margin-top:14px">Checkout string format: <code>SignatureKey;CID;CartID;AmountWithoutDot;Currency</code></p>';
$content .= '<p class="meta">Callback string format: <code>SignatureKey;CID;POID;CartID;AmountWithoutDot;Currency;Status</code></p>';
$content .= '</div>';
$content .= '</div>';

gkash_render_page('GKash Hash Tester', $content);

