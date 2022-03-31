<?php

namespace Pexess\Http;

class Response
{

    public function status(int $response_code): Response
    {
        http_response_code($response_code);
        return $this;
    }

    public function send(string $message): void
    {
        echo $message;
    }

    public function end(string $message = ""): never
    {
        exit($message);
    }

    /**
     * Exit with an error
     * @throws \Exception
     */
    public function quit(int $status_code, string $message ="")
    {
        $this->status($status_code);
        throw new \Exception($message,$status_code);
    }

    public function redirect(string $url): void
    {
        header("Location: $url");
    }

    public function header(string $header) : void
    {
        header($header);
    }

    public function json($data): never
    {
        $this->header('Content-Type: application/json');
        $json = json_encode($data);
        exit($json);
    }
}