<?php

namespace Pexess\Common;

class Headers
{

    public function __construct()
    {
        $headers = $this->getHeaders();

        foreach ($headers as $key => $value) {
            $this->$key = $value;
        }
    }

    private function getHeaders(): array
    {
        $headers = array();
        foreach ($_SERVER as $key => $value) {
            if (!str_starts_with($key, 'HTTP_')) {
                continue;
            }
            $header = str_replace(' ', '_', str_replace('-', '_', strtolower(substr($key, 5))));
            $headers[$header] = $value;
        }
        return $headers;
    }
}