<?php

echo "<?php\n";
echo "require_once __DIR__ . '/../bootstrap.php';\n\n";
echo "\$runtime = gkash_runtime();\n";
echo "\$checkout = \$runtime['checkout'];\n";
echo "\$result = \$checkout->submit(array(\n";
echo "    'name' => 'Aina Rahman',\n";
echo "    'email' => 'aina@example.com',\n";
echo "    'phone' => '0123456789',\n";
echo "    'item_name' => 'Sample Product',\n";
echo "    'item_description' => 'Demo order',\n";
echo "    'quantity' => 1,\n";
echo "    'item_total' => '10.00',\n";
echo "    'extra_charges' => '0.00',\n";
echo "    'final_amount' => '10.00',\n";
echo "    'order_id' => 'ORDER1001',\n";
echo "    'payment_method' => 'card',\n";
echo "));\n\n";
echo "if (\$result['mode'] === 'gateway') {\n";
echo "    echo \$result['form_html'];\n";
echo "} else {\n";
echo "    header('Location: ' . \$result['redirect_url']);\n";
echo "}\n";
