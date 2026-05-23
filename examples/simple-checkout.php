<?php

require_once __DIR__ . '/../public/_shared.php';

$runtime = gkash_runtime();
$config = $runtime['config'];
$defaultOrderId = 'ORDER-' . gmdate('YmdHis') . '-' . strtoupper(substr(md5(uniqid('', true)), 0, 6));

$content = '';
$content .= '<div class="hero">';
$content .= '<div class="card">';
$content .= '<span class="tag">Sample checkout</span>';
$content .= '<h1 class="title">Create a GKash payment request</h1>';
$content .= '<p class="sub">Fill in the sample order fields below. The checkout handler will validate the amount, build the gateway payload, and either redirect to the live GKash endpoint or run in local demo mode when the endpoint is not configured.</p>';
$content .= '<form action="../public/checkout.php" method="post" style="margin-top:18px">';
$content .= '<div class="grid">';
$fields = array(
    'name' => array('label' => 'Name', 'value' => 'Aina Rahman'),
    'email' => array('label' => 'Email', 'value' => 'aina@example.com'),
    'phone' => array('label' => 'Phone', 'value' => '0123456789'),
    'item_name' => array('label' => 'Item / Product Name', 'value' => 'Gundam Model Kit'),
    'item_description' => array('label' => 'Item Description', 'value' => 'Limited edition sample order'),
    'quantity' => array('label' => 'Quantity', 'value' => '1'),
    'item_total' => array('label' => 'Item Total', 'value' => '10.00'),
    'extra_charges' => array('label' => 'Extra Charges', 'value' => '0.00'),
    'final_amount' => array('label' => 'Final Amount', 'value' => '10.00'),
    'order_id' => array('label' => 'Order ID', 'value' => $defaultOrderId),
    'address' => array('label' => 'Address', 'value' => 'Kuala Lumpur'),
    'remark' => array('label' => 'Remark', 'value' => 'Sample checkout for GKash'),
    'custom_parameter' => array('label' => 'Custom Parameter', 'value' => 'demo=1'),
);

foreach ($fields as $name => $meta) {
    $type = 'text';
    if (in_array($name, array('item_total', 'extra_charges', 'final_amount'))) {
        $type = 'number';
    } elseif ($name === 'quantity') {
        $type = 'number';
    } elseif ($name === 'remark' || $name === 'address') {
        $content .= '<div class="field" style="grid-column:1/-1"><label for="' . gkash_h($name) . '">' . gkash_h($meta['label']) . '</label><textarea id="' . gkash_h($name) . '" name="' . gkash_h($name) . '">' . gkash_h($meta['value']) . '</textarea></div>';
        continue;
    }
    $content .= '<div class="field"><label for="' . gkash_h($name) . '">' . gkash_h($meta['label']) . '</label><input id="' . gkash_h($name) . '" type="' . $type . '" name="' . gkash_h($name) . '" value="' . gkash_h($meta['value']) . '"></div>';
}

$content .= '<div class="field"><label for="payment_method">Payment Method</label><select id="payment_method" name="payment_method"><option value="card">Card payment</option><option value="ewallet">eWallet payment</option></select></div>';
$content .= '<div class="field"><label for="currency">Currency</label><input id="currency" name="currency" value="' . gkash_h($config['currency']) . '"></div>';
$content .= '</div>';
$content .= '<div class="row" style="margin-top:18px">';
$content .= '<button class="btn primary" type="submit">Submit payment</button>';
$content .= '<a class="btn secondary" href="../public/index.php">Home</a>';
$content .= '</div>';
$content .= '</form>';
$content .= '</div>';

$content .= '<div class="card">';
$content .= '<h2 style="margin-top:0">Gateway notes</h2>';
$content .= '<ul style="margin:0;padding-left:18px;line-height:1.7;color:#4b4238">';
$content .= '<li>Card payments use preselection <code>ECOMM</code></li>';
$content .= '<li>eWallet payments use preselection <code>EWALLET</code></li>';
$content .= '<li>Signature format: <code>SignatureKey;CID;CartID;AmountWithoutDot;Currency</code></li>';
$content .= '<li>Callback verification adds <code>POID</code> and <code>Status</code> to the signature string</li>';
$content .= '</ul>';
$content .= '</div>';
$content .= '</div>';

gkash_render_page('GKash Sample Checkout', $content);

