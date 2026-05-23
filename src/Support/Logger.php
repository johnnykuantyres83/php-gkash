<?php

namespace Support;

class Logger
{
    protected $directory;
    protected $channel;

    public function __construct($directory, $channel = 'gkash')
    {
        $this->directory = rtrim($directory, '/\\');
        $this->channel = $channel;

        if (!is_dir($this->directory)) {
            @mkdir($this->directory, 0777, true);
        }
    }

    public function debug($message, array $context = array())
    {
        $this->write('DEBUG', $message, $context);
    }

    public function info($message, array $context = array())
    {
        $this->write('INFO', $message, $context);
    }

    public function error($message, array $context = array())
    {
        $this->write('ERROR', $message, $context);
    }

    public function callback($message, array $context = array())
    {
        $this->write('CALLBACK', $message, $context);
    }

    public function write($level, $message, array $context = array())
    {
        $file = $this->directory . DIRECTORY_SEPARATOR . $this->channel . '-' . gmdate('Y-m-d') . '.log';
        $line = '[' . gmdate('Y-m-d H:i:s') . '] [' . strtoupper($level) . '] ' . $message;

        if (!empty($context)) {
            $encoded = json_encode($context);
            if ($encoded !== false) {
                $line .= ' ' . $encoded;
            }
        }

        $line .= PHP_EOL;
        @file_put_contents($file, $line, FILE_APPEND | LOCK_EX);
    }
}

