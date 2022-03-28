<?php

namespace Pexess\Validator;

use Pexess\Database\Database;

class Validator
{
    public static function validate(array $body, array $data): array|bool
    {
        $errors = [];
        foreach ($data as $key => $rules) {
            $rules = explode("|", $rules);
            foreach ($rules as $rule) {
                if (Rules::required->check($rule) && !array_key_exists($key, $errors)) {
                    if (empty($body[$key])) $errors[$key] = Rules::required->message("", $key);
                }
                if (Rules::unique->check($rule) && !array_key_exists($key, $errors)) {
                    $table = substr($rule, strpos($rule, ':') + 1);
                    if (Database::from($table)->count([
                        "where" => [
                            $key => $body[$key]
                        ]
                    ])) $errors[$key] = Rules::unique->message("", $key);
                }
                if (Rules::email->check($rule) && !array_key_exists($key, $errors)) {
                    if (!filter_var($body[$key], FILTER_VALIDATE_EMAIL)) $errors[$key] = Rules::email->message("", $key);
                }
                if (Rules::exist->check($rule) && !array_key_exists($key, $errors)) {
                    $table = substr($rule, strpos($rule, ':') + 1);
                    if (!Database::from($table)->count([
                        "where" => [
                            $key => $body[$key]
                        ]
                    ])) $errors[$key] = Rules::exist->message("", $key);
                }
                if (Rules::min->check($rule) && !array_key_exists($key, $errors)) {
                    $min = substr($rule, strpos($rule, ':') + 1);
                    if (strlen($body[$key]) < $min) $errors[$key] = Rules::min->message("", $key, $min);
                }
                if (Rules::max->check($rule) && !array_key_exists($key, $errors)) {
                    $max = substr($rule, strpos($rule, ':') + 1);
                    if (strlen($body[$key]) > $max) $errors[$key] = Rules::max->message("", $key, $max);
                }
            }
        }
        return $errors ?? true;
    }
}