<?php

namespace Linkedcode\Slim\Request;

use Exception;

abstract class AbstractRequest
{
    public const TYPE_STRING = 'string';
    public const TYPE_INT = 'integer';
    public const TYPE_FLOAT = 'float';

    public static function get(array $data, string $attribute, string $type, string|int|float $default)
    {
        switch ($type) {
            case self::TYPE_STRING:
                if (!isset($data[$attribute])) {
                    $value = $default;
                } else if (empty($data[$attribute])) {
                    $value = $default;
                } else {
                    $value = (string) $data[$attribute];
                }

                return $value;
            case self::TYPE_INT:
                if (!isset($data[$attribute])) {
                    $value = $default;
                } else if ($data[$attribute] === "") {
                    $value = $default;
                } else {
                    $value = (int) $data[$attribute];
                }

                return $value;
            case self::TYPE_FLOAT:
                if (!isset($data[$attribute])) {
                    $value = $default;
                } else if ($data[$attribute] === "") {
                    $value = $default;
                } else {
                    $value = (int) $data[$attribute];
                }

                return $value;
            default:
                throw new Exception("Falta programar tipo para request");
        }
    }
}