<?php

namespace Linkedcode\Slim\Request;

use Exception;
use Linkedcode\Slim\Exception\ValidationException;

class RequestValidator
{
    protected const MIN_LENGTH = 'minLength';
    protected const MAX_LENGTH = 'maxLength';
    protected const REQUIRED = 'required';
    protected const MAXIMUM = 'maximum';
    protected const MINIMUM = 'minimum';
    protected const TYPE = 'type';

    protected const TYPE_STRING = 'string';
    protected const TYPE_INTEGER = 'integer';
    protected const TYPE_NUMBER = 'number'; // float

    protected array $invalidParams = [];

    protected array $rules = [];

    public function validate(array $data): array
    {
        foreach ($this->rules as $field => $fieldRules) {
            $data[$field] = $this->validateField($field, $fieldRules, $data);
        }

        $this->checkViolations();

        return $data;
    }

    private function validateField(string $field, array $rules, array $data)
    {
        if (isset($data[$field])) {
            $value = $data[$field];
        } else {
            $value = $this->getEmptyValue($rules[self::TYPE]);
        }

        foreach ($rules as $rule => $ruleValue) {
            switch ($rule) {
                case self::REQUIRED:
                    $this->validateRequired($field, $value);
                    break;
                case self::MIN_LENGTH:
                    $this->validateMinLength($field, $value, $ruleValue);
                    break;
                case self::MAX_LENGTH:
                    $this->validateMaxLength($field, $value, $ruleValue);
                    break;
                case self::TYPE:
                    $value = $this->validateType($field, $value, $ruleValue);
                    break;
                default:
                    throw new Exception("Falta programar `{$rule}`");
            }
        }

        return $value;
    }

    private function validateRequired(string $field, mixed $value): mixed
    {
        if (is_null($value) || $value === "") {
            $this->addInvalidParam($field, "El valor es requerido.");
        }

        return $value;
    }

    private function validateMinLength(string $field, mixed $value, int $minLength): mixed
    {
        if (strlen($value) < $minLength) {
            $this->addInvalidParam($field, "La longitud mínima es de {$minLength} caracteres.");
        }

        return $value;
    }

    private function validateMaxLength(string $field, mixed $value, int $maxLength): mixed
    {
        if (strlen($value) > $maxLength) {
            $this->addInvalidParam($field, "La longitud máxima es de {$maxLength} caracteres.");
        }

        return $value;
    }

    private function validateType(string $field, mixed $value, string $type): mixed
    {
        switch ($type) {
            case self::TYPE_INTEGER:
                return intval($value);
            case self::TYPE_NUMBER:
                return floatval($value);
            case self::TYPE_STRING:
                return (string) $value;
        }

        return $value;
    }

    private function addInvalidParam(string $name, string $reason): void
    {
        $this->invalidParams[] = array('name' => $name, 'reason' => $reason);
    }

    private function checkViolations()
    {
        if (false === empty($this->invalidParams)) {
            throw new ValidationException("Errores de validacion", $this->invalidParams);
        }
    }

    private function getEmptyValue(string $type): mixed
    {
        switch ($type) {
            case self::TYPE_INTEGER:
                return 0;
            case self::TYPE_NUMBER:
                return 0.0;
            case self::TYPE_STRING:
                return "";
        }
    }
}