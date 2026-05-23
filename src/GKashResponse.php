<?php

class GKashResponse
{
    protected $data = array();

    public function __construct(array $data = array())
    {
        $defaults = array(
            'success' => false,
            'status' => '',
            'order_id' => '',
            'transaction_id' => '',
            'amount' => '',
            'currency' => '',
            'signature_valid' => false,
            'message' => '',
            'duplicate' => false,
            'requery' => null,
            'raw' => array(),
            'validation_errors' => array(),
            'payment_method' => '',
        );

        $this->data = array_merge($defaults, $data);
    }

    public function toArray()
    {
        return $this->data;
    }

    public function toJson()
    {
        return json_encode($this->data);
    }

    public function __get($name)
    {
        return array_key_exists($name, $this->data) ? $this->data[$name] : null;
    }

    public function __set($name, $value)
    {
        $this->data[$name] = $value;
    }
}

