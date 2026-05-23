<?php

require_once __DIR__ . '/../bootstrap.php';

$sample = array(
    'poid' => 'PO123456',
    'cartid' => 'ORDER1001',
    'amount' => '10.00',
    'currency' => 'MYR',
    'status' => '88 - Transferred',
    'signature' => 'sha512_callback_signature_here',
);

echo "<?php\n";
echo "// Sample callback payload for GKash\n";
echo '$payload = ' . var_export($sample, true) . ";\n";
echo "// Pass payload to GKashCallbackHandler::handle(\$payload);\n";

