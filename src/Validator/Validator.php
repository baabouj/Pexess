<?php

namespace Pexess\Validator;

use Closure;
use Pexess\Database\Database;
use Pexess\Exceptions\HttpException;
use Pexess\Helpers\StatusCodes;
use Pexess\Http\Request;
use Pexess\Http\Response;

class Validator
{
    public static function validate(array|object $data, array $rules): array
    {
        $errors = [];

        if (is_object($data)) {
            $data = get_object_vars($data);
        }

        foreach ($rules as $field => $fieldRules) {
            $fieldRules = explode("|", $fieldRules);


            $isRequired = in_array('required', $fieldRules);

            if (!$isRequired && !array_key_exists($field, $data)) {
                continue;
            }

            $value = $data[$field];

            foreach ($fieldRules as $rule) {
                [$name, $args] = explode(':', $rule);

                if (!method_exists(self::class, $name)) continue;

                $args = $args ? explode(',', $args) : [];

                $args[] = $field;

                $result = call_user_func([self::class, $name], $value, ...$args);

                if (is_string($result)) {
                    $errors[$field] = str_replace('{field}', $field, $result);
                    break;
                }
            }
        }

        return $errors;
    }

    public static function body(array $rules, string|HttpException $exception = ''): Closure
    {
        return function (Request $req, Response $res, Closure $next) use ($rules, $exception) {
            $errors = self::validate($req->all(), $rules);

            $res->throwUnless(!$errors, !empty($exception) ? $exception : new HttpException([
                'statusCode' => StatusCodes::BAD_REQUEST,
                'message' => 'Bad Request',
                'errors' => $errors
            ], StatusCodes::BAD_REQUEST));

            $next();
        };
    }

    public static function params(array $rules, string|HttpException $exception = ''): Closure
    {
        return function (Request $req, Response $res, Closure $next) use ($rules, $exception) {
            $errors = self::validate($req->params(), $rules);

            $res->throwUnless(!$errors, !empty($exception) ? $exception : new HttpException([
                'statusCode' => StatusCodes::BAD_REQUEST,
                'message' => 'Bad Request',
                'errors' => $errors
            ], StatusCodes::BAD_REQUEST));

            $next();
        };
    }

    protected static function required(mixed $value): bool|string
    {
        if (empty($value)) return "{field} is required";
        return true;
    }

    protected static function email(mixed $value): bool|string
    {
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) return "{field} must be a valid email address";
        return true;
    }

    protected static function phone(mixed $value): bool|string
    {
        if (!preg_match("/^\+?[0-9]{10,12}$/", $value)) return "{field} must be a valid phone number";
        return true;
    }

    protected static function url(mixed $value): bool|string
    {
        if (!filter_var($value, FILTER_VALIDATE_URL)) return "{field} must be a valid url";
        return true;
    }

    protected static function file(mixed $value, string $format = ''): bool|string
    {
        $name = is_array($value) ? $value["tmp_name"] : $value;
        if (!is_file($name)) return "{field} must be a file";
        if (!empty($format)) {
            $name = is_array($value) ? $value["name"] : $value;
            if (pathinfo($name, PATHINFO_EXTENSION) != $format) {
                return "{field} must be a file of type $format";
            }
        }
        return true;
    }

    protected static function image(mixed $value): bool|string
    {
        is_array($value) && $value = $value["tmp_name"];
        if (!getimagesize($value)) return "{field} must be an image";
        return true;
    }

    protected static function unique(mixed $value, string $table, string $column): bool|string
    {
        if (Database::from($table)->count([
            "where" => [
                $column => $value
            ]
        ])) return "{field} must be unique";
        return true;
    }

    protected static function exist(mixed $value, string $table, string $column): bool|string
    {
        if (!Database::from($table)->count([
            "where" => [
                $column => $value
            ]
        ])) return "{field} doesn't exist";
        return true;
    }

    protected static function length(mixed $value, int $length): bool|string
    {
        if (strlen($value) != $length) return "{field} must be $length characters long";
        return true;
    }

    protected static function between(mixed $value, int $min, int $max): bool|string
    {
        if (strlen($value) < $min || strlen($value) > $max) return "{field} must be between $min and $max characters long";
        return true;
    }

    protected static function min(mixed $value, int $min): bool|string
    {
        if (strlen($value) < $min) return "{field} must be at least $min characters long";
        return true;
    }

    protected static function max(mixed $value, int $max): bool|string
    {
        if (strlen($value) > $max) return "{field} must be at most $max characters long";
        return true;
    }

    protected static function number(mixed $value): bool|string
    {
        if (!is_numeric($value)) return "{field} must be a number";
        return true;
    }

    protected static function int(mixed $value): bool|string
    {
        if (!is_int($value)) return "{field} must be an integer";
        return true;
    }

    protected static function float(mixed $value): bool|string
    {
        if (!is_float($value)) return "{field} must be a float";
        return true;
    }

    protected static function string(mixed $value): bool|string
    {
        if (!is_string($value)) return "{field} must be a string";
        return true;
    }

    protected static function bool(mixed $value): bool|string
    {
        if (!is_bool($value)) return "{field} must be a boolean";
        return true;
    }

    protected static function array(mixed $value): bool|string
    {
        if (!is_array($value)) return "{field} must be an array";
        return true;
    }

    protected static function object(mixed $value): bool|string
    {
        if (!is_object($value)) return "{field} must be an object";
        return true;
    }

    protected static function null(mixed $value): bool|string
    {
        if (!is_null($value)) return "{field} must be null";
        return true;
    }

    protected static function empty(mixed $value): bool|string
    {
        if (!empty($value)) return "{field} must be empty";
        return true;
    }

    protected static function eq(mixed $value, mixed $value2): bool|string
    {
        if ($value != $value2) return "{field} must be equal to $value2";
        return true;
    }

    protected static function gt(mixed $value, mixed $value2): bool|string
    {
        if ($value <= $value2) return "{field} must be greater than $value2";
        return true;
    }

    protected static function gte(mixed $value, mixed $value2): bool|string
    {
        if ($value < $value2) return "{field} must be greater than or equal to $value2";
        return true;
    }

    protected static function lt(mixed $value, mixed $value2): bool|string
    {
        if ($value >= $value2) return "{field} must be less than $value2";
        return true;
    }

    protected static function lte(mixed $value, mixed $value2): bool|string
    {
        if ($value > $value2) return "{field} must be less than or equal to $value2";
        return true;
    }

    protected static function in(mixed $value, array $array): bool|string
    {
        if (!in_array($value, $array)) return "{field} must be in " . implode(", ", $array);
        return true;
    }

    protected static function regex(mixed $value, string $regex): bool|string
    {
        if (!preg_match($regex, $value)) return "{field} must match $regex pattern";
        return true;
    }

    protected static function date(mixed $value): bool|string
    {
        if (!strtotime($value)) return "{field} must be a valid date";
        return true;
    }

    protected static function time(mixed $value): bool|string
    {
        if (!strtotime($value)) return "{field} must be a valid time";
        return true;
    }

    protected static function format(mixed $value, string $format): bool|string
    {
        if (!date($format, strtotime($value))) return "{field} must be a valid date in $format format";
        return true;
    }

    protected static function contains(mixed $value, string $string): bool|string
    {
        if (!strpos($value, $string)) return "{field} must contain $string";
        return true;
    }

    protected static function sw(mixed $value, string $string): bool|string
    {
        if (!str_starts_with($value, $string)) return "{field} must start with $string";
        return true;
    }

    protected static function ew(mixed $value, string $string): bool|string
    {
        if (!str_ends_with($value, $string)) return "{field} must end with $string";
        return true;
    }

    protected static function not(mixed $value, string $rule): bool|string
    {
        if (!is_string(call_user_func([self::class, $rule], $value))) return "{field} is invalid";
        return true;
    }

}