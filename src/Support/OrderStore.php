<?php

namespace Support;

class OrderStore
{
    protected $directory;

    public function __construct($directory)
    {
        $this->directory = rtrim($directory, '/\\');
        if (!is_dir($this->directory)) {
            @mkdir($this->directory, 0777, true);
        }
    }

    public function saveOrder(array $order)
    {
        if (!isset($order['order_id']) || $order['order_id'] === '') {
            return false;
        }

        $record = $this->loadOrder($order['order_id']);
        if (!is_array($record)) {
            $record = array();
        }

        $record['order'] = $order;
        $record['updated_at'] = gmdate('c');
        if (!isset($record['created_at'])) {
            $record['created_at'] = gmdate('c');
        }

        return $this->writeRecord($order['order_id'], $record);
    }

    public function saveCallback($orderId, array $callback)
    {
        if ($orderId === '') {
            return false;
        }

        $record = $this->loadOrder($orderId);
        if (!is_array($record)) {
            $record = array(
                'created_at' => gmdate('c'),
            );
        }

        $record['callback'] = $callback;
        $record['updated_at'] = gmdate('c');

        if (isset($record['order']) && is_array($record['order'])) {
            $record['order']['last_callback_at'] = gmdate('c');
        }

        return $this->writeRecord($orderId, $record);
    }

    public function loadOrder($orderId)
    {
        $file = $this->filePath($orderId);
        if (!is_file($file)) {
            return array();
        }

        $json = file_get_contents($file);
        $data = json_decode($json, true);
        return is_array($data) ? $data : array();
    }

    public function markReplay($orderId, $transactionId, $signature, $ttl = 86400)
    {
        $key = sha1($orderId . '|' . $transactionId . '|' . $signature);
        $file = $this->replayPath($key);
        if (is_file($file)) {
            $age = time() - filemtime($file);
            if ($age <= $ttl) {
                return true;
            }
        }

        $record = array(
            'order_id' => $orderId,
            'transaction_id' => $transactionId,
            'signature' => $signature,
            'created_at' => gmdate('c'),
        );
        @file_put_contents($file, json_encode($record), LOCK_EX);
        return false;
    }

    protected function writeRecord($orderId, array $record)
    {
        $file = $this->filePath($orderId);
        $json = json_encode($record, JSON_PRETTY_PRINT);
        if ($json === false) {
            return false;
        }

        return file_put_contents($file, $json, LOCK_EX) !== false;
    }

    protected function filePath($orderId)
    {
        return $this->directory . DIRECTORY_SEPARATOR . gkash_safe_filename($orderId) . '.json';
    }

    protected function replayPath($key)
    {
        $dir = dirname($this->directory) . DIRECTORY_SEPARATOR . 'replay';
        if (!is_dir($dir)) {
            @mkdir($dir, 0777, true);
        }

        return $dir . DIRECTORY_SEPARATOR . $key . '.json';
    }
}

