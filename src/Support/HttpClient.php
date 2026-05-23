<?php

namespace Support;

class HttpClient
{
    protected $timeout;
    protected $connectTimeout;
    protected $userAgent;

    public function __construct(array $options = array())
    {
        $this->timeout = isset($options['timeout']) ? (int) $options['timeout'] : 30;
        $this->connectTimeout = isset($options['connect_timeout']) ? (int) $options['connect_timeout'] : 10;
        $this->userAgent = isset($options['user_agent']) ? $options['user_agent'] : 'GKash-PHP/1.0';
    }

    public function postForm($url, array $fields, array $headers = array())
    {
        return $this->request('POST', $url, $fields, $headers);
    }

    public function get($url, array $headers = array())
    {
        return $this->request('GET', $url, array(), $headers);
    }

    public function request($method, $url, array $fields = array(), array $headers = array())
    {
        if (function_exists('curl_init')) {
            return $this->requestWithCurl($method, $url, $fields, $headers);
        }

        return $this->requestWithStreams($method, $url, $fields, $headers);
    }

    protected function requestWithCurl($method, $url, array $fields = array(), array $headers = array())
    {
        $ch = curl_init();
        $options = array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => $this->connectTimeout,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_USERAGENT => $this->userAgent,
            CURLOPT_HTTPHEADER => array_merge(array(
                'Accept: application/json, text/plain, */*',
                'Content-Type: application/x-www-form-urlencoded',
            ), $headers),
        );

        if (strtoupper($method) === 'POST') {
            $options[CURLOPT_POST] = true;
            $options[CURLOPT_POSTFIELDS] = http_build_query($fields, '', '&');
        }

        curl_setopt_array($ch, $options);
        $body = curl_exec($ch);
        $error = curl_error($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);

        return array(
            'success' => $error === '',
            'status_code' => isset($info['http_code']) ? (int) $info['http_code'] : 0,
            'body' => $body === false ? '' : $body,
            'error' => $error,
            'info' => $info,
            'raw' => null,
        );
    }

    protected function requestWithStreams($method, $url, array $fields = array(), array $headers = array())
    {
        $content = '';
        $headerLines = array('Accept: application/json, text/plain, */*', 'Content-Type: application/x-www-form-urlencoded');
        foreach ($headers as $header) {
            $headerLines[] = $header;
        }

        $context = array(
            'http' => array(
                'method' => strtoupper($method),
                'timeout' => $this->timeout,
                'header' => implode("\r\n", $headerLines),
            ),
        );

        if (strtoupper($method) === 'POST') {
            $context['http']['content'] = http_build_query($fields, '', '&');
        }

        $body = @file_get_contents($url, false, stream_context_create($context));
        $statusCode = 0;
        if (isset($http_response_header) && is_array($http_response_header)) {
            foreach ($http_response_header as $line) {
                if (preg_match('/^HTTP\/\S+\s+(\d+)/', $line, $matches)) {
                    $statusCode = (int) $matches[1];
                    break;
                }
            }
        }

        return array(
            'success' => $body !== false,
            'status_code' => $statusCode,
            'body' => $body === false ? '' : $body,
            'error' => $body === false ? 'Stream request failed.' : '',
            'info' => array(),
            'raw' => null,
        );
    }
}

