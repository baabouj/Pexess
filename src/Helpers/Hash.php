<?php

namespace Pexess\Helpers;

class Hash
{
    public static function hash(string $secret,int|null|string $algo = PASSWORD_DEFAULT): string
    {
        return password_hash($secret, $algo);
    }

    public static function compare(string $secret,string $hash): bool
    {
        return password_verify($secret, $hash);
    }
}