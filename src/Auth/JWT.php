<?php

namespace Pexess\Auth;

class JWT
{
    public static function generate(array $payload,string $key): string
    {
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);

        $payload = json_encode($payload);

        $base64UrlHeader = self::base64url_encode($header);

        $base64UrlPayload = self::base64url_encode($payload);

        $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, $key, true);

        $base64UrlSignature = self::base64url_encode($signature);

        return $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
    }

    public static function verify(string $token, string $key): array|false
    {
        $token = explode(".", $token);
        $payload = json_decode(self::base64url_decode($token[1]),true);
        $signature = hash_hmac('sha256', $token[0] . "." . $token[1], $key, true);

        $base64UrlSignature = self::base64url_encode($signature);

        if (hash_equals($token[2], $base64UrlSignature)) return $payload;
        return false;
    }

    private static function base64url_encode(string $string): string
    {
        return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($string));
    }

    private static function base64url_decode(string $string): string
    {
        return str_replace(['-', '_', ''], ['+', '/', '='], base64_decode($string));
    }
}