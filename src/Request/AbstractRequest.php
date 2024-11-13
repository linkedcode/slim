<?php

namespace Linkedcode\Slim\Request;

use DomainException;
use Exception;
use Linkedcode\Slim\Exception\ValidationException;
use Psr\Http\Message\ServerRequestInterface;

abstract class AbstractRequest
{
    private const TYPE_STRING = 'string';
    public const TYPE_INT = 'integer';
    public const TYPE_FLOAT = 'float';

    protected const RULES = array();

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

    public static function createFromRequest(ServerRequestInterface $request): static
    {
        return static::fromArray($request->getParsedBody());
    }

    public static function fromArray(array $data): static
    {
        $request = new static;

        foreach ($data as $key => $value) {
            if (property_exists($request, $key)) {
                $request->$key = $value;
            }
        }

        return $request;
    }

    /*protected static function validate(array $data): array
    {
        //$newdata = [];
        $invalidParams = [];

        foreach (static::RULES as $field => $fieldRules) {
            try {
                $value = null;

                if (isset($data[$field])) {
                    $value = $data[$field];
                }

                foreach ($fieldRules as $rule => $ruleValue) {
                    switch ($rule) {
                        case 'required':
                            $data[$field] = static::validateRequired($value);
                            break;
                        default:
                            throw new Exception("Falta programar");
                    }
                }
            } catch (DomainException $e) {
                $invalidParams[] = array('name' => $field, 'reason' => $e->getMessage());
            }
        }

        if (false === empty($invalidParams)) {
            throw new ValidationException("Errores de validacion", $invalidParams);
        }

        return $data;
    }

    private static function validateRequired(mixed $value)
    {
        if (is_null($value) || $value === "") {
            throw new Exception("El valor es requerido.");
        }

        return $value;
    }*/
}