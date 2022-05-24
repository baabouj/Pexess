<?php

namespace Pexess\Auth;

use Pexess\Exceptions\UnauthorizedException;

class JWT
{
    public static function generate(array $payload, string $key, array $options = []): string
    {
        $header = json_encode(['alg' => 'HS256', 'typ' => 'JWT']);

        $payload["iat"] = time();
        $payload["exp"] = $options['expires'] ?? time() + 60 * 15;

        $payload = json_encode($payload);

        $base64UrlHeader = self::base64url_encode($header);

        $base64UrlPayload = self::base64url_encode($payload);

        $signature = self::hmac_sha256($base64UrlHeader . "." . $base64UrlPayload, $key);

        $base64UrlSignature = self::base64url_encode($signature);

        return $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
    }

    public static function verify(string $token, string $key, bool $ignore_expiration = false): array|false
    {
        $token = explode(".", $token);
        if (count($token) != 3) {
            throw new UnauthorizedException();
        }
        $payload = json_decode(self::base64url_decode($token[1]), true);
        $signature = self::hmac_sha256($token[0] . "." . $token[1], $key);

        $base64UrlSignature = self::base64url_encode($signature);

        if (hash_equals($token[2], $base64UrlSignature)) {
            if (!$ignore_expiration && ($payload["exp"] < time())) {
                throw new UnauthorizedException();
            }
            return $payload;
        }

        throw new UnauthorizedException();
    }

    private static function base64url_encode(string $string): string
    {
        return rtrim(strtr(base64_encode($string), '+/', '-_'), '=');
    }

    private static function base64url_decode(string $string): string
    {
        return base64_decode(strtr($string, '-_', '+/'));
    }

    private static function hmac_sha256(string $data, string $key): bool|string
    {
        return hash_hmac('sha256', $data, $key, true);
    }
}