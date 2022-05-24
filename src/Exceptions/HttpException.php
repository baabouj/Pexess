<?php

namespace Pexess\Exceptions;

use Pexess\Http\Response;

class HttpException extends \Exception
{
    public function __construct(public string|array $response, public int $statusCode)
    {
        is_array($this->response) ? $this->message = '' : $this->message = $this->response;
        parent::__construct($this->message, $this->statusCode);
    }

    public function handle(Response $res)
    {
        $response = is_array($this->response) ? $this->response : [
            'statusCode' => $this->statusCode,
            'message' => $this->message
        ];

        $res->status($this->statusCode)->json($response);
    }

}