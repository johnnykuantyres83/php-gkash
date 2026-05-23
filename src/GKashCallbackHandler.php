<?php

use Support\Logger;
use Support\OrderStore;

class GKashCallbackHandler
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

    public function handle(array $request, array $options = array())
    {
        $payload = $this->normalizeRequest($request);
        $validationErrors = array();

        foreach (array('poid', 'cartid', 'amount', 'currency', 'status', 'signature') as $field) {
            if (!isset($payload[$field]) || $payload[$field] === '') {
                $validationErrors[] = 'Missing field: ' . $field;
            }
        }

        $order = array();
        if ($this->store && isset($payload['cartid']) && $payload['cartid'] !== '') {
            $order = $this->store->loadOrder($payload['cartid']);
        }

        $signatureValid = false;
        if (empty($validationErrors)) {
            $signatureValid = GKashSignature::verifyCallbackSignature(
                $this->client->getConfig('signature_key', ''),
                $this->client->getConfig('cid', ''),
                $payload['poid'],
                $payload['cartid'],
                $payload['amount'],
                $payload['currency'],
                $payload['status'],
                $payload['signature']
            );

            if ($signatureValid && $this->client->getConfig('security.verify_amount', true) && isset($order['amount']) && $order['amount'] !== '') {
                $signatureValid = GKashSignature::normalizeAmount($order['amount']) === GKashSignature::normalizeAmount($payload['amount']);
            }

            if ($signatureValid && $this->client->getConfig('security.verify_currency', true) && isset($order['currency']) && $order['currency'] !== '') {
                $signatureValid = strtoupper($order['currency']) === strtoupper($payload['currency']);
            }
        }

        $duplicate = false;
        if ($this->store && isset($payload['cartid'], $payload['poid'], $payload['signature'])) {
            $duplicate = $this->store->markReplay($payload['cartid'], $payload['poid'], $payload['signature'], (int) $this->client->getConfig('security.replay_ttl', 86400));
        }

        $requery = null;
        $success = false;
        $message = '';
        if (!empty($validationErrors)) {
            $message = implode('; ', $validationErrors);
        } elseif (!$signatureValid) {
            $message = 'Signature verification failed.';
        } else {
            $success = GKashSignature::isSuccessfulStatus($payload['status']);
            $message = $success ? 'Payment transferred.' : 'Payment not successful.';
        }

        if ($signatureValid && !$success && !empty($options['allow_requery'])) {
            $requery = $this->client->requeryPaymentStatus(
                $payload['cartid'],
                $payload['amount'],
                $payload['currency']
            );

            if (isset($requery['parsed']['status']) && GKashSignature::isSuccessfulStatus($requery['parsed']['status'])) {
                $success = true;
                $message = 'Payment confirmed by requery.';
            }
        }

        $response = new GKashResponse(array(
            'success' => $success,
            'status' => GKashSignature::normalizeStatus($payload['status']),
            'order_id' => $payload['cartid'],
            'transaction_id' => $payload['poid'],
            'amount' => GKashSignature::normalizeAmount($payload['amount']),
            'currency' => $payload['currency'],
            'signature_valid' => $signatureValid,
            'message' => $message,
            'duplicate' => $duplicate,
            'requery' => $requery,
            'raw' => $request,
            'validation_errors' => $validationErrors,
            'payment_method' => isset($order['payment_method']) ? $order['payment_method'] : '',
        ));

        if ($this->store) {
            $this->store->saveCallback($response->order_id, $response->toArray());
        }

        if ($this->logger) {
            $this->logger->callback('Callback handled', $response->toArray());
        }

        return $response;
    }

    protected function normalizeRequest(array $request)
    {
        $source = array();
        foreach ($request as $key => $value) {
            $source[strtolower((string) $key)] = is_array($value) ? $value : gkash_sanitize_text($value);
        }

        $status = isset($source['status']) ? $source['status'] : '';
        $source['status'] = $status;
        $source['cartid'] = isset($source['cartid']) ? $source['cartid'] : (isset($source['v_cartid']) ? $source['v_cartid'] : '');
        $source['poid'] = isset($source['poid']) ? $source['poid'] : (isset($source['gatewaytransid']) ? $source['gatewaytransid'] : '');
        $source['amount'] = isset($source['amount']) ? $source['amount'] : (isset($source['v_amount']) ? $source['v_amount'] : '');
        $source['currency'] = isset($source['currency']) ? $source['currency'] : (isset($source['v_currency']) ? $source['v_currency'] : $this->client->getConfig('currency', 'MYR'));
        $source['signature'] = isset($source['signature']) ? $source['signature'] : '';

        return $source;
    }
}

