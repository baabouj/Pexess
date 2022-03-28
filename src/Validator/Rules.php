<?php

namespace Pexess\Validator;

enum Rules: string
{
    case required = 'required';
    case unique = "unique";
    case email = "email";
    case exist = "exist";
    case match = "match";
    case max = "max";
    case min = "min";

    public function check(string $rule): bool
    {
        return match ($this) {
            self::required => $rule == 'required',
            self::email => $rule == 'email',
            self::unique => str_starts_with($rule, 'unique'),
            self::exist => str_starts_with($rule, 'exist'),
            self::match => str_starts_with($rule, 'match'),
            self::max => str_starts_with($rule, 'max'),
            self::min => str_starts_with($rule, 'min'),
        };
    }

    public function message(string $message = '', $field = 'field', $other = ''): string
    {
        return match ($this) {
            self::required => $message ?: "$field is required!",
            self::unique => $message ?: "this $field already exist",
            self::email => $message ?: "$field must be a valid email address",
            self::exist => $message ?: "this $field does not exist",
            self::match => $message ?: "$field must match $other",
            self::max => $message ?: "$field must be at most $other character length",
            self::min => $message ?: "$field must be at least $other character length"
        };
    }
}