<?php

use Support\HttpClient;
use Support\Logger;

class GKashClient
{
    protected $config;
    protected $logger;
    protected $httpClient;

    public function __construct(array $config, Logger $logger = null, HttpClient $httpClient = null)
    {
        $this->config = $config;
        $this->logger = $logger;
        $this->httpClient = $httpClient ? $httpClient : new HttpClient($config['http']);
    }

    public function getConfig($key, $default = null)
    {
        $segments = explode('.', $key);
        $value = $this->config;

        foreach ($segments as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return $default;
            }
            $value = $value[$segment];
        }

        return $value;
    }

    public function isDemoMode()
    {
        return (bool) $this->getConfig('demo_mode', true) || $this->getCheckoutEndpoint() === '';
    }

    public function getEnvironment()
    {
        return $this->getConfig('environment', 'sandbox');
    }

    public function getCheckoutEndpoint()
    {
        $endpoint = (string) $this->getConfig('checkout_endpoint', '');
        if ($endpoint !== '') {
            return $endpoint;
        }

        $environment = $this->getEnvironment();
        $nested = $this->getConfig($environment . '.checkout_endpoint', '');
        return (string) $nested;
    }

    public function getQueryEndpoint()
    {
        $endpoint = (string) $this->getConfig('query_endpoint', '');
        if ($endpoint !== '') {
            return $endpoint;
        }

        $environment = $this->getEnvironment();
        return (string) $this->getConfig($environment . '.query_endpoint', 'https://api.gkash.my/api/payment/query');
    }

    public function getReturnUrl(array $order = array())
    {
        $configured = (string) $this->getConfig('return_url', '');
        if ($configured !== '') {
            return $this->appendOrderQuery($configured, $order);
        }

        return $this->appendOrderQuery(gkash_default_return_url(), $order);
    }

    public function getCallbackUrl(array $order = array())
    {
        $configured = (string) $this->getConfig('callback_url', '');
        if ($configured !== '') {
            return $this->appendOrderQuery($configured, $order);
        }

        return $this->appendOrderQuery(gkash_default_callback_url(), $order);
    }

    public function appendOrderQuery($url, array $order = array())
    {
        if (!isset($order['order_id']) || $order['order_id'] === '') {
            return $url;
        }

        $separator = (strpos($url, '?') === false) ? '?' : '&';
        return $url . $separator . 'order_id=' . rawurlencode($order['order_id']);
    }

    public function getPaymentMethodPreselection($paymentMethod)
    {
        $paymentMethod = strtolower(trim((string) $paymentMethod));
        $map = $this->getConfig('preselection', array());

        if ($paymentMethod === 'ewallet') {
            return isset($map['ewallet']) ? $map['ewallet'] : 'EWALLET';
        }

        return isset($map['card']) ? $map['card'] : 'ECOMM';
    }

    public function createCheckoutSignature(array $order)
    {
        return GKashSignature::generateCheckoutSignature(
            $this->getConfig('signature_key', ''),
            $this->getConfig('cid', ''),
            $order['order_id'],
            $order['amount'],
            $order['currency']
        );
    }

    public function createCallbackSignature(array $payload)
    {
        return GKashSignature::generateCallbackSignature(
            $this->getConfig('signature_key', ''),
            $this->getConfig('cid', ''),
            $payload['poid'],
            $payload['cartid'],
            $payload['amount'],
            $payload['currency'],
            $payload['status']
        );
    }

    public function buildCheckoutPayload(array $order)
    {
        $paymentMethod = isset($order['payment_method']) ? $order['payment_method'] : $this->getConfig('payment_method', 'card');
        $payload = array(
            'preselection' => $this->getPaymentMethodPreselection($paymentMethod),
            'version' => $this->getConfig('version', '1.0'),
            'CID' => $this->getConfig('cid', ''),
            'v_currency' => $order['currency'],
            'v_amount' => GKashSignature::normalizeAmount($order['amount']),
            'v_cartid' => $order['order_id'],
            'v_firstname' => $order['customer_name'],
            'v_lastname' => $order['customer_name'],
            'v_billemail' => $order['customer_email'],
            'v_billphone' => $order['customer_phone'],
            'v_productdesc' => $order['product_description'],
            'v_productno' => isset($order['product_no']) ? $order['product_no'] : 'AP001',
            'returnurl' => $this->getReturnUrl($order),
            'callbackurl' => $this->getCallbackUrl($order),
            'signature' => $this->createCheckoutSignature($order),
        );

        if (!empty($order['address'])) {
            $payload['v_billaddress'] = $order['address'];
        }

        if (!empty($order['remark'])) {
            $payload['remark'] = $order['remark'];
        }

        if (!empty($order['custom_parameter'])) {
            $payload['custom_parameter'] = $order['custom_parameter'];
        }

        return $payload;
    }

    public function renderCheckoutForm(array $order)
    {
        $action = $this->getCheckoutEndpoint();
        $payload = $this->buildCheckoutPayload($order);

        $html = array();
        $html[] = '<!DOCTYPE html>';
        $html[] = '<html lang="en">';
        $html[] = '<head>';
        $html[] = '<meta charset="utf-8">';
        $html[] = '<meta name="viewport" content="width=device-width, initial-scale=1">';
        $html[] = '<title>Redirecting to GKash</title>';
        $html[] = '<style>';
        $html[] = 'body{font-family:Arial,sans-serif;background:#f6f2ea;color:#222;margin:0;padding:40px;}';
        $html[] = '.card{max-width:720px;margin:0 auto;background:#fff;border:1px solid #e5dccb;border-radius:16px;padding:28px;box-shadow:0 18px 50px rgba(0,0,0,.06);}';
        $html[] = 'h1{margin-top:0;font-size:28px;}';
        $html[] = 'code,pre{background:#f7f4ef;border-radius:8px;padding:2px 6px;}';
        $html[] = 'button{background:#17324d;color:#fff;border:0;border-radius:10px;padding:12px 18px;font-size:16px;cursor:pointer;}';
        $html[] = '</style>';
        $html[] = '</head>';
        $html[] = '<body>';
        $html[] = '<div class="card">';
        $html[] = '<h1>Processing payment</h1>';
        $html[] = '<p>Your order has been prepared for GKash checkout.</p>';
        $html[] = '<p><strong>Order ID:</strong> ' . htmlspecialchars($order['order_id'], ENT_QUOTES, 'UTF-8') . '</p>';
        $html[] = '<p><strong>Amount:</strong> ' . htmlspecialchars(GKashSignature::normalizeAmount($order['amount']), ENT_QUOTES, 'UTF-8') . ' ' . htmlspecialchars($order['currency'], ENT_QUOTES, 'UTF-8') . '</p>';

        if ($action === '') {
            $html[] = '<p><strong>Demo mode:</strong> checkout endpoint is not configured yet.</p>';
        } else {
            $html[] = '<form id="gkashPaymentForm" action="' . htmlspecialchars($action, ENT_QUOTES, 'UTF-8') . '" method="post">';
            foreach ($payload as $name => $value) {
                $html[] = '<input type="hidden" name="' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '" value="' . htmlspecialchars($value, ENT_QUOTES, 'UTF-8') . '">';
            }
            $html[] = '<noscript><button type="submit">Continue to GKash</button></noscript>';
            $html[] = '</form>';
            $html[] = '<p>If you are not redirected automatically, click continue.</p>';
            $html[] = '<button type="submit" form="gkashPaymentForm">Continue to GKash</button>';
            $html[] = '<script>document.getElementById("gkashPaymentForm").submit();</script>';
        }

        $html[] = '</div>';
        $html[] = '</body>';
        $html[] = '</html>';

        return implode("\n", $html);
    }

    public function requeryPaymentStatus($orderId, $amount, $currency = null)
    {
        $currency = $currency ? $currency : $this->getConfig('currency', 'MYR');
        $signature = GKashSignature::generateCheckoutSignature(
            $this->getConfig('signature_key', ''),
            $this->getConfig('cid', ''),
            $orderId,
            $amount,
            $currency
        );

        $fields = array(
            'version' => $this->getConfig('version', '1.0'),
            'cartid' => $orderId,
            'amount' => GKashSignature::normalizeAmount($amount),
            'currency' => $currency,
            'signature' => $signature,
            'CID' => $this->getConfig('cid', ''),
        );

        $result = $this->httpClient->postForm($this->getQueryEndpoint(), $fields);
        $result['parsed'] = $this->parseResponseBody($result['body']);
        return $result;
    }

    public function parseResponseBody($body)
    {
        $body = trim((string) $body);
        if ($body === '') {
            return array();
        }

        $json = json_decode($body, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($json)) {
            return $json;
        }

        $parsed = array();
        parse_str($body, $parsed);
        if (!empty($parsed)) {
            return $parsed;
        }

        return array('raw' => $body);
    }

    protected function log($level, $message, array $context = array())
    {
        if ($this->logger) {
            $this->logger->write($level, $message, $context);
        }
    }
}

