<?php

namespace Linkedcode\Slim\Request;

use Exception;
use Linkedcode\Slim\Exception\ValidationException;

class RequestValidator
{
    private array $invalidParams;

    private array $rules;

    public function validate(array $data): array
    {
        $newdata = [];

        foreach ($this->rules as $field => $fieldRules) {
            $newdata[$field] = $this->validateField($field, $fieldRules, $data);
        }

        $this->checkViolations();

        return $newdata;
    }

    private function validateField(string $field, array $rules, array $data)
    {
        $value = null;

        if (isset($data[$field])) {
            $value = $data[$field];
        }

        foreach ($rules as $rule => $ruleValue) {
            switch ($rule) {
                case 'required': 
                    return $this->validateRequired($field, $value);
                default:
                    throw new Exception("Falta programar");
            }
        }
    }

    private function validateRequired(string $field, mixed $value): mixed
    {
        if (is_null($value) || $value === "") {
            $this->addInvalidParam($field, "El valor es requerido.");
        }

        return $value;
    }

    private function addInvalidParam(string $name, string $reason)
    {
        $this->invalidParams = array('name' => $name, 'reason' => $reason);
    }

    private function checkViolations()
    {
        if (false === empty($this->invalidParams)) {
            throw new ValidationException("Errores de validacion", $this->invalidParams);
        }
    }
}