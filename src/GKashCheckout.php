<?php

use Support\Logger;
use Support\OrderStore;

class GKashCheckout
{
    protected $client;
    protected $logger;
    protected $store;

    public function __construct(GKashClient $client, Logger $logger = null, OrderStore $store = null)
    {
        $this->client = $client;
        $this->logger = $logger;
        $this->store = $store;
    }

    public function prepareOrder(array $input)
    {
        $customerName = gkash_sanitize_text(isset($input['name']) ? $input['name'] : '');
        $customerEmail = gkash_sanitize_text(isset($input['email']) ? $input['email'] : '');
        $customerPhone = gkash_sanitize_text(isset($input['phone']) ? $input['phone'] : '');
        $productName = gkash_sanitize_text(isset($input['item_name']) ? $input['item_name'] : '');
        $productDesc = gkash_sanitize_text(isset($input['item_description']) ? $input['item_description'] : '');
        $quantity = max(1, (int) (isset($input['quantity']) ? $input['quantity'] : 1));
        $itemTotal = (float) (isset($input['item_total']) ? $input['item_total'] : 0);
        $extraCharges = (float) (isset($input['extra_charges']) ? $input['extra_charges'] : 0);
        $finalAmount = isset($input['final_amount']) ? (float) $input['final_amount'] : ($itemTotal + $extraCharges);
        $computedFinalAmount = round($itemTotal + $extraCharges, 2);

        if (abs($finalAmount - $computedFinalAmount) > 0.009) {
            $finalAmount = $computedFinalAmount;
        }

        $orderId = gkash_sanitize_text(isset($input['order_id']) ? $input['order_id'] : '');
        if ($orderId === '') {
            $orderId = $this->generateOrderId();
        }

        $currency = gkash_sanitize_text(isset($input['currency']) ? $input['currency'] : $this->client->getConfig('currency', 'MYR'));
        if ($currency === '') {
            $currency = $this->client->getConfig('currency', 'MYR');
        }

        $paymentMethod = gkash_sanitize_text(isset($input['payment_method']) ? $input['payment_method'] : $this->client->getConfig('payment_method', 'card'));
        if ($paymentMethod !== 'ewallet') {
            $paymentMethod = 'card';
        }

        $order = array(
            'order_id' => $orderId,
            'customer_name' => $customerName,
            'customer_email' => $customerEmail,
            'customer_phone' => $customerPhone,
            'product_name' => $productName,
            'product_description' => $productDesc !== '' ? $productDesc : $productName,
            'quantity' => $quantity,
            'item_total' => GKashSignature::normalizeAmount($itemTotal),
            'extra_charges' => GKashSignature::normalizeAmount($extraCharges),
            'amount' => GKashSignature::normalizeAmount($finalAmount),
            'currency' => $currency,
            'payment_method' => $paymentMethod,
            'address' => gkash_sanitize_text(isset($input['address']) ? $input['address'] : ''),
            'remark' => gkash_sanitize_text(isset($input['remark']) ? $input['remark'] : ''),
            'custom_parameter' => gkash_sanitize_text(isset($input['custom_parameter']) ? $input['custom_parameter'] : ''),
            'product_no' => gkash_sanitize_text(isset($input['product_no']) ? $input['product_no'] : 'AP001'),
            'source' => 'sample-checkout',
            'created_at' => gmdate('c'),
        );

        if ($order['customer_name'] === '' || $order['customer_email'] === '' || $order['customer_phone'] === '' || $order['product_name'] === '') {
            throw new InvalidArgumentException('Name, email, phone, item name, and order ID are required.');
        }

        return $order;
    }

    public function submit(array $input)
    {
        $order = $this->prepareOrder($input);

        if ($this->store) {
            $this->store->saveOrder($order);
        }

        if ($this->client->isDemoMode()) {
            $callback = array(
                'success' => true,
                'status' => '88',
                'order_id' => $order['order_id'],
                'transaction_id' => 'DEMO-' . strtoupper(substr(md5($order['order_id']), 0, 12)),
                'amount' => $order['amount'],
                'currency' => $order['currency'],
                'signature_valid' => true,
                'message' => 'Demo payment completed locally.',
                'duplicate' => false,
                'requery' => null,
                'raw' => array(),
                'validation_errors' => array(),
                'payment_method' => $order['payment_method'],
            );

            if ($this->store) {
                $this->store->saveCallback($order['order_id'], $callback);
            }

            if ($this->logger) {
                $this->logger->callback('Demo callback stored for order ' . $order['order_id']);
            }

            return array(
                'mode' => 'demo',
                'order' => $order,
                'redirect_url' => 'return.php?order_id=' . rawurlencode($order['order_id']) . '&demo=1',
                'callback' => $callback,
            );
        }

        return array(
            'mode' => 'gateway',
            'order' => $order,
            'form_html' => $this->client->renderCheckoutForm($order),
        );
    }

    public function generateOrderId()
    {
        $suffix = strtoupper(substr(md5(uniqid('', true)), 0, 10));
        return 'GKASH-' . gmdate('YmdHis') . '-' . $suffix;
    }
}

