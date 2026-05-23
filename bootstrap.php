<?php

if (!defined('GKASH_ROOT')) {
    define('GKASH_ROOT', __DIR__);
}

if (!defined('GKASH_CONFIG_FILE')) {
    define('GKASH_CONFIG_FILE', GKASH_ROOT . '/config/gkash.php');
}

if (!defined('GKASH_LOCAL_CONFIG_FILE')) {
    define('GKASH_LOCAL_CONFIG_FILE', GKASH_ROOT . '/config/gkash.local.php');
}

if (!defined('GKASH_CACHE_DIR')) {
    define('GKASH_CACHE_DIR', GKASH_ROOT . '/cache');
}

if (!defined('GKASH_LOG_DIR')) {
    define('GKASH_LOG_DIR', GKASH_ROOT . '/logs');
}

function gkash_base_path($path = '')
{
    $path = ltrim($path, '/\\');
    return $path === '' ? GKASH_ROOT : GKASH_ROOT . DIRECTORY_SEPARATOR . str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $path);
}

function gkash_ensure_directory($path)
{
    if (!is_dir($path)) {
        @mkdir($path, 0777, true);
    }
    return is_dir($path);
}

function gkash_array_merge_recursive_distinct(array $base, array $override)
{
    foreach ($override as $key => $value) {
        if (is_array($value) && isset($base[$key]) && is_array($base[$key])) {
            $base[$key] = gkash_array_merge_recursive_distinct($base[$key], $value);
        } else {
            $base[$key] = $value;
        }
    }

    return $base;
}

function gkash_load_config()
{
    static $config = null;

    if ($config !== null) {
        return $config;
    }

    $config = array();

    if (is_file(GKASH_CONFIG_FILE)) {
        $base = include GKASH_CONFIG_FILE;
        if (is_array($base)) {
            $config = $base;
        }
    }

    if (is_file(GKASH_LOCAL_CONFIG_FILE)) {
        $local = include GKASH_LOCAL_CONFIG_FILE;
        if (is_array($local)) {
            $config = gkash_array_merge_recursive_distinct($config, $local);
        }
    }

    $defaults = array(
        'environment' => 'sandbox',
        'demo_mode' => true,
        'version' => '1.0',
        'currency' => 'MYR',
        'cid' => '',
        'signature_key' => '',
        'checkout_endpoint' => '',
        'query_endpoint' => 'https://api.gkash.my/api/payment/query',
        'return_url' => '',
        'callback_url' => '',
        'base_url' => '',
        'log_path' => GKASH_LOG_DIR,
        'cache_path' => GKASH_CACHE_DIR,
        'payment_method' => 'card',
        'preselection' => array(
            'card' => 'ECOMM',
            'ewallet' => 'EWALLET',
        ),
        'http' => array(
            'timeout' => 30,
            'connect_timeout' => 10,
            'user_agent' => 'GKash-PHP/1.0',
        ),
        'security' => array(
            'verify_amount' => true,
            'verify_currency' => true,
            'replay_ttl' => 86400,
        ),
        'display' => array(
            'debug_raw_callback' => false,
        ),
    );

    $config = gkash_array_merge_recursive_distinct($defaults, $config);

    gkash_ensure_directory($config['log_path']);
    gkash_ensure_directory($config['cache_path']);
    gkash_ensure_directory($config['cache_path'] . '/orders');
    gkash_ensure_directory($config['cache_path'] . '/replay');

    return $config;
}

function gkash_config($key = null, $default = null)
{
    $config = gkash_load_config();

    if ($key === null) {
        return $config;
    }

    $segments = explode('.', $key);
    $value = $config;

    foreach ($segments as $segment) {
        if (!is_array($value) || !array_key_exists($segment, $value)) {
            return $default;
        }
        $value = $value[$segment];
    }

    return $value;
}

function gkash_default_return_url()
{
    $configured = gkash_config('return_url', '');
    if ($configured !== '') {
        return $configured;
    }

    return 'http://localhost/php-gkash/public/return.php';
}

function gkash_default_callback_url()
{
    $configured = gkash_config('callback_url', '');
    if ($configured !== '') {
        return $configured;
    }

    return 'http://localhost/php-gkash/public/callback.php';
}

function gkash_sanitize_text($value)
{
    $value = is_string($value) ? $value : '';
    $value = trim($value);
    $value = preg_replace('/[\\x00-\\x1F\\x7F]/', '', $value);
    return $value;
}

function gkash_safe_filename($value)
{
    $value = preg_replace('/[^A-Za-z0-9._-]+/', '_', (string) $value);
    $value = trim($value, '._-');
    return $value === '' ? 'gkash' : $value;
}

function gkash_autoload($class)
{
    $class = ltrim($class, '\\');
    $file = GKASH_ROOT . '/src/' . str_replace('\\', '/', $class) . '.php';
    if (is_file($file)) {
        require_once $file;
    }
}

spl_autoload_register('gkash_autoload');
