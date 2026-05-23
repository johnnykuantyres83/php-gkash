<?php

require_once __DIR__ . '/_shared.php';

$runtime = gkash_runtime();
$checkout = $runtime['checkout'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../examples/simple-checkout.php');
    exit;
}

try {
    $result = $checkout->submit($_POST);
} catch (Exception $e) {
    $runtime['logger']->error('Checkout failed', array('error' => $e->getMessage()));
    gkash_render_page(
        'GKash Checkout Error',
        '<div class="card"><h1 style="margin-top:0">Checkout error</h1><div class="notice fail">' . gkash_h($e->getMessage()) . '</div><p><a class="btn primary" href="../examples/simple-checkout.php">Back to checkout</a></p></div>'
    );
    exit;
}

if ($result['mode'] === 'demo') {
    header('Location: ' . $result['redirect_url']);
    exit;
}

echo $result['form_html'];

