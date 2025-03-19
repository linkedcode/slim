<?php

namespace Linkedcode\Slim\Request;

use Exception;
use Linkedcode\Slim\ApiProblem\ApiProblem;
use Psr\Http\Message\ServerRequestInterface;

abstract class AbstractRequest
{
    protected const MIN_LENGTH = 'minLength';
    protected const MAX_LENGTH = 'maxLength';
    protected const MAXIMUM = 'maximum';
    protected const MINIMUM = 'minimum';
    protected const IN = 'in';

    protected const TYPE = 'type';
    protected const SUBTYPE = 'subtype';

    protected const OPTIONAL = 'optional';
    protected const REQUIRED = 'required';

    protected const TYPE_STRING = 'string';
    protected const TYPE_INTEGER = 'integer';
    protected const TYPE_NUMBER = 'number'; // float
    protected const TYPE_BOOL = 'boolean';

    protected const SUB_TYPE_URL = 'url';

    protected array $invalidParams = [];

    protected array $rules = [];

    private function __construct() {}

    public static function fromRequest(ServerRequestInterface $request): static
    {
        return static::fromArray($request->getParsedBody());
    }

    public static function fromArray(array $data): static
    {
        $request = new static;
        $request->validate($data);

        foreach ($data as $key => $value) {
            if (property_exists($request, $key)) {
                $request->$key = $value;
            }
        }

        return $request;
    }

    protected function validate(array $data): void
    {
        foreach ($this->getRules() as $field => $rules) {
            if ($this->isOptional($field)) {
                if (!isset($data[$field]) || $data[$field] === "") {
                    continue;
                }
            }

            if ($this->isRequired($field) && !isset($data[$field])) {
                $this->addInvalidParam($field, "El campo `{$field}` es requerido");
                continue;
            }

            $this->validateField($field, $rules, $data[$field]);
        }

        $this->checkViolations();
    }

    private function addInvalidParam(string $name, string $reason): void
    {
        $this->invalidParams[] = ['name' => $name, 'reason' => $reason];
    }

    private function getRules(): array
    {
        return $this->rules;
    }

    private function validateField(string $field, array $rules, mixed $value)
    {
        foreach ($rules as $rule => $ruleValue) {
            switch ($rule) {
                case self::MIN_LENGTH:
                    $this->validateMinLength($field, $value, $ruleValue);
                    break;
                case self::MAX_LENGTH:
                    $this->validateMaxLength($field, $value, $ruleValue);
                    break;
                case self::MINIMUM:
                    $this->validateMinimum($field, $value, $ruleValue);
                    break;
                case self::MAXIMUM:
                    $this->validateMaximum($field, $value, $ruleValue);
                    break;
                case self::IN:
                    $this->validateIn($field, $value, $ruleValue);
                    break;
                case self::TYPE:
                    $this->validateType($field, $value, $ruleValue);
                    break;
                case self::SUBTYPE:
                    $this->validateSubType($field, $value, $ruleValue);
                    break;
                default:
                    throw new Exception("Falta programar regla de validacion `{$rule}`");
            }
        }
    }

    private function isRequired(string $field): bool
    {
        return isset($this->rules[$field][self::REQUIRED]);
    }

    private function isOptional(string $field): bool
    {
        return $this->isRequired($field) ? false : true;
    }

    private function validateMinLength(string $field, string $value, int $min): void
    {
        if (strlen($value) < $min) {
            $this->addInvalidParam($field, sprintf(
                "La longitud mínima es de %d caracteres.",
                $min
            ));
        }
    }

    private function validateMinimum(string $field, int $value, int $min): void
    {
        if ($value < $min) {
            $this->addInvalidParam($field, "El valor mínimo es de {$min}.");
        }
    }

    private function validateMaximum(string $field, int $value, int $max): void
    {
        if ($value > $max) {
            $this->addInvalidParam($field, "El valor máximo es de {$max}.");
        }
    }

    private function validateMaxLength(string $field, string $value, int $maxLength): void
    {
        if (strlen($value) > $maxLength) {
            $this->addInvalidParam($field, "La longitud máxima es de {$maxLength} caracteres.");
        }
    }

    private function validateIn(string $field, string $value, array $validValues): void
    {
        $validValuesKeys = array_keys($validValues);
        $validValuesNames = array_values($validValues);

        foreach ($validValuesKeys as &$validValue) {
            $validValue = strtolower($validValue);
        }

        $value = strtolower($value);

        if (!in_array($value, $validValuesKeys)) {
            $msg = "El valor '{$value}' no es valido. Valores válidos: " . implode(", ", $validValuesNames);
            $this->addInvalidParam($field, $msg);
        }
    }

    private function validateSubType(string $field, mixed $value, string $subType): void
    {
        switch ($subType) {
            case self::SUB_TYPE_URL:
                $this->validateUrl($field, $value);
                break;
            default:
                throw new Exception("Falta programar regla de validacion");
        }
    }

    private function validateType(string $field, mixed $value, string $type): void
    {
        $actual = gettype($value);

        if ($actual === $type) {
            return;
        }

        if ($actual === 'double' && $type === self::TYPE_NUMBER) {
            return;
        }

        if ($actual === 'integer' && $type === self::TYPE_NUMBER) {
            return;
        }

        $msg = sprintf(
            "El campo `%s` debe ser de tipo `%s`, se recibió tipo `%s`",
            $field,
            $type,
            $actual
        );

        $this->addInvalidParam($field, $msg);
    }

    private function validateUrl(string $field, string $url)
    {
        $url = filter_var($url, FILTER_SANITIZE_URL);

        if (filter_var($url, FILTER_VALIDATE_URL)) {
            $scheme = parse_url($url, PHP_URL_SCHEME);
            if (in_array($scheme, ['https', 'http'])) {
                return true;
            }
        }

        $this->addInvalidParam($field, "No es una URL válida, recuerde incluir http(s).");
    }

    private function checkViolations()
    {
        if (false === empty($this->invalidParams)) {
            $apiProblem = new ApiProblem(ApiProblem::TYPE_VALIDATION_ERROR, 422);
            $apiProblem->setErrors($this->invalidParams);
            $apiProblem->throw();
        }
    }
}
