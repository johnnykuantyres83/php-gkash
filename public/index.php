<?php

require_once __DIR__ . '/_shared.php';

$content = '';
$content .= '<div class="hero">';
$content .= '<div class="card">';
$content .= '<span class="tag">GKash PHP Gateway</span>';
$content .= '<h1 class="title">Standalone GKash integration for PHP 5.6+</h1>';
$content .= '<p class="sub">This repository rebuilds the legacy GKash flow into a reusable payment gateway package with signature verification, callback handling, optional requery support, and a sample checkout form.</p>';
$content .= '<div class="row" style="margin-top:18px">';
$content .= '<a class="btn primary" href="../examples/simple-checkout.php">Open sample checkout</a>';
$content .= '<a class="btn secondary" href="../examples/order-integration.php">View integration sample</a>';
$content .= '</div>';
$content .= '<p class="meta" style="margin-top:18px">Configure credentials in <code>config/gkash.local.php</code>. Demo mode will simulate a local success flow when no gateway endpoint is configured.</p>';
$content .= '</div>';

$content .= '<div class="card">';
$content .= '<h2 style="margin-top:0">What is included</h2>';
$content .= '<ul style="margin:0;padding-left:18px;line-height:1.7;color:#4b4238">';
$content .= '<li>Checkout form for card and eWallet payments</li>';
$content .= '<li>SHA512 signature generation and verification</li>';
$content .= '<li>Callback requery and replay protection helper</li>';
$content .= '<li>Structured response object and daily logs</li>';
$content .= '<li>Success and failure pages for the sample flow</li>';
$content .= '</ul>';
$content .= '</div>';
$content .= '</div>';

gkash_render_page('GKash PHP Gateway', $content);
