<?php

namespace Linkedcode\Slim\Request;

use Exception;
use Linkedcode\Slim\ApiProblem\ApiProblem;
use Linkedcode\Slim\ApiProblem\ApiProblemException;

class RequestValidator
{
    protected const MIN_LENGTH = 'minLength';
    protected const MAX_LENGTH = 'maxLength';
    protected const REQUIRED = 'required';
    protected const MAXIMUM = 'maximum';
    protected const MINIMUM = 'minimum';
    protected const IN = 'in';
    protected const TYPE = 'type';
    protected const OPTIONAL = 'optional';

    protected const TYPE_STRING = 'string';
    protected const TYPE_INTEGER = 'integer';
    protected const TYPE_NUMBER = 'number'; // float

    protected array $invalidParams = [];

    protected array $rules = [];

    public function validate(array $data): array
    {
        $this->conditionalRules($data);

        foreach ($this->getRules() as $field => $fieldRules) {
            if (isset($fieldRules[self::OPTIONAL])) {
                // Si es opcional y no hay valor, lo dejamos seguir,
                // pero si hay valor lo validamos normalmente.
                if (!isset($data[$field]) || is_null($data[$field])) {
                    continue;
                }
            }

            $data[$field] = $this->validateField($field, $fieldRules, $data);
        }

        $this->checkViolations();

        return $data;
    }

    protected function conditionalRules(array $data): void
    {
    }

    protected function getRules(): array
    {
        return $this->rules;
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

    private function validateMinimum(string $field, mixed $value, int $minimum): mixed
    {
        if (intval($value) < $minimum) {
            $this->addInvalidParam($field, "El valor mínimo es de {$minimum}.");
        }

        return $value;
    }

    private function validateMaximum(string $field, mixed $value, int $maximum): mixed
    {
        if (intval($value) > $maximum) {
            $this->addInvalidParam($field, "El valor máximo es de {$maximum}.");
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

    private function validateIn(string $field, string $value, array $validValues): mixed
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
            $apiProblem = new ApiProblem(ApiProblem::TYPE_VALIDATION_ERROR, 422);
            $apiProblem->setErrors($this->invalidParams);
            
            throw new ApiProblemException($apiProblem);
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