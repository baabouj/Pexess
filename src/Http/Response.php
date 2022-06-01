<?php

namespace Pexess\Http;

use Pexess\Exceptions\HttpException;
use Pexess\Helpers\StatusCodes;

class Response
{

    public function status(int $response_code): Response
    {
        http_response_code($response_code);
        return $this;
    }

    public function send($response): void
    {
        if (is_array($response) || is_object($response)) {
            $this->json($response);
        }
        echo $response;
    }

    public function end($message = null): never
    {
        die($message);
    }

    public function throw(string|HttpException $exception)
    {
        is_string($exception) && throw new $exception();
        throw $exception;
    }

    public function throwIf(bool $condition, string|HttpException $exception)
    {
        if ($condition) {
            $this->throw($exception);
        }
    }

    public function throwUnless(bool $condition, string|HttpException $exception)
    {
        if (!$condition) {
            $this->throw($exception);
        }
    }

    public function quit(string|array $response, int $statusCode = StatusCodes::BAD_REQUEST)
    {
        throw new HttpException($response, $statusCode);
    }

    public function quitIf(bool $condition, string|array $response, int $statusCode = StatusCodes::BAD_REQUEST)
    {
        if ($condition) {
            $this->quit($response, $statusCode);
        }
    }

    public function quitUnless(bool $condition, string|array $response, int $statusCode = StatusCodes::BAD_REQUEST)
    {
        if (!$condition) {
            $this->quit($response, $statusCode);
        }
    }

    public function redirect(string $url, int $statusCode = StatusCodes::MOVED_TEMPORARILY): void
    {
        $this->status($statusCode);
        header("Location: $url");
    }

    public function header(string $header): void
    {
        header($header);
    }

    public function cookie(string $key, string $value, array $options = []): void
    {
        setcookie($key, $value, $options);
    }

    public function json($data): void
    {
        $this->type('application/json');
        $json = json_encode($data);
        echo $json;
    }

    public function headersSent(): bool
    {
        return headers_sent();
    }

    public function type(string $type): void
    {
        $this->header('Content-Type: ' . $type);
    }

    public function continue(): void
    {
        $this->status(StatusCodes::CONTINUE);
        $this->send(null);
    }

    public function switchingProtocols(): void
    {
        $this->status(StatusCodes::SWITCHING_PROTOCOLS);
        $this->send(null);
    }

    public function ok($body): void
    {
        $this->status(StatusCodes::OK);
        $this->send($body);
    }

    public function created($body): void
    {
        $this->status(StatusCodes::CREATED);
        $this->send($body);
    }

    public function accepted($body): void
    {
        $this->status(StatusCodes::ACCEPTED);
        $this->send($body);
    }

    public function nonAuthoritativeInformation($body): void
    {
        $this->status(StatusCodes::NON_AUTHORITATIVE_INFORMATION);
        $this->send($body);
    }

    public function noContent(): void
    {
        $this->status(StatusCodes::NO_CONTENT);
        $this->send(null, false);
    }

    public function resetContent(): void
    {
        $this->status(StatusCodes::RESET_CONTENT);
        $this->send(null, false);
    }

    public function partialContent($body): void
    {
        $this->status(StatusCodes::PARTIAL_CONTENT);
        $this->send($body);
    }

    public function multipleChoices($body): void
    {
        $this->status(StatusCodes::MULTIPLE_CHOICES);
        $this->send($body);
    }

    public function movedPermanently($body): void
    {
        $this->status(StatusCodes::MOVED_PERMANENTLY);
        $this->send($body);
    }

    public function movedTemporarily($body): void
    {
        $this->status(StatusCodes::MOVED_TEMPORARILY);
        $this->send($body);
    }

    public function seeOther($body): void
    {
        $this->status(StatusCodes::SEE_OTHER);
        $this->send($body);
    }

    public function notModified($body): void
    {
        $this->status(StatusCodes::NOT_MODIFIED);
        $this->send($body);
    }

    public function useProxy($body): void
    {
        $this->status(StatusCodes::USE_PROXY);
        $this->send($body);
    }

    public function temporaryRedirect($body): void
    {
        $this->status(StatusCodes::TEMPORARY_REDIRECT);
        $this->send($body);
    }

    public function badRequest($body): void
    {
        $this->status(StatusCodes::BAD_REQUEST);
        $this->send($body);
    }

    public function unauthorized($body): void
    {
        $this->status(StatusCodes::UNAUTHORIZED);
        $this->send($body);
    }

    public function paymentRequired($body): void
    {
        $this->status(StatusCodes::PAYMENT_REQUIRED);
        $this->send($body);
    }

    public function forbidden($body): void
    {
        $this->status(StatusCodes::FORBIDDEN);
        $this->send($body);
    }

    public function notFound($body): void
    {
        $this->status(StatusCodes::NOT_FOUND);
        $this->send($body);
    }

    public function methodNotAllowed($body): void
    {
        $this->status(StatusCodes::METHOD_NOT_ALLOWED);
        $this->send($body);
    }

    public function notAcceptable($body): void
    {
        $this->status(StatusCodes::NOT_ACCEPTABLE);
        $this->send($body);
    }

    public function proxyAuthenticationRequired($body): void
    {
        $this->status(StatusCodes::PROXY_AUTHENTICATION_REQUIRED);
        $this->send($body);
    }

    public function requestTimeout($body): void
    {
        $this->status(StatusCodes::REQUEST_TIMEOUT);
        $this->send($body);
    }

    public function conflict($body): void
    {
        $this->status(StatusCodes::CONFLICT);
        $this->send($body);
    }

    public function gone($body): void
    {
        $this->status(StatusCodes::GONE);
        $this->send($body);
    }

    public function lengthRequired($body): void
    {
        $this->status(StatusCodes::LENGTH_REQUIRED);
        $this->send($body);
    }

    public function preconditionFailed($body): void
    {
        $this->status(StatusCodes::PRECONDITION_FAILED);
        $this->send($body);
    }

    public function requestTooLarge($body): void
    {
        $this->status(StatusCodes::REQUEST_TOO_LONG);
        $this->send($body);
    }

    public function requestUriTooLong($body): void
    {
        $this->status(StatusCodes::REQUEST_URI_TOO_LONG);
        $this->send($body);
    }

    public function unsupportedMediaType($body): void
    {
        $this->status(StatusCodes::UNSUPPORTED_MEDIA_TYPE);
        $this->send($body);
    }

    public function requestedRangeNotSatisfiable($body): void
    {
        $this->status(StatusCodes::REQUESTED_RANGE_NOT_SATISFIABLE);
        $this->send($body);
    }

    public function expectationFailed($body): void
    {
        $this->status(StatusCodes::EXPECTATION_FAILED);
        $this->send($body);
    }

    public function unprocessableEntity($body): void
    {
        $this->status(StatusCodes::UNPROCESSABLE_ENTITY);
        $this->send($body);
    }

    public function tooManyRequests($body): void
    {
        $this->status(StatusCodes::TOO_MANY_REQUESTS);
        $this->send($body);
    }

    public function internalServerError($body): void
    {
        $this->status(StatusCodes::INTERNAL_SERVER_ERROR);
        $this->send($body);
    }

    public function notImplemented($body): void
    {
        $this->status(StatusCodes::NOT_IMPLEMENTED);
        $this->send($body);
    }

    public function badGateway($body): void
    {
        $this->status(StatusCodes::BAD_GATEWAY);
        $this->send($body);
    }

    public function serviceUnavailable($body): void
    {
        $this->status(StatusCodes::SERVICE_UNAVAILABLE);
        $this->send($body);
    }

    public function gatewayTimeout($body): void
    {
        $this->status(StatusCodes::GATEWAY_TIMEOUT);
        $this->send($body);
    }

    public function httpVersionNotSupported($body): void
    {
        $this->status(StatusCodes::HTTP_VERSION_NOT_SUPPORTED);
        $this->send($body);
    }

}