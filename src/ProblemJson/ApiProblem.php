<?php

namespace Linkedcode\Slim\ProblemJson;

class ApiProblem
{
    private string $title;
    private int $statusCode;
    private array $errors = [];
    private string $instance = '';
    private string $type = '';

    public function __construct(string $title, int $statusCode)
    {
        $this->title = $title;
        $this->statusCode = $statusCode;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function setErrors(array $errors): void
    {
        $this->errors = $errors;
    }

    public function addError(array $error): void
    {
        $this->errors[] = $error;
    }

    public function setInstance(string $instance)
    {
        $this->instance = $instance;
    }

    public function setType(string $type)
    {
        $this->type = $type;
    }

    public function getBody(): string
    {
        $data = array(
            'title' => $this->getTitle(),
            'status' => $this->getStatusCode()
        );

        if (!empty($this->errors)) {
            $data['invalid-params'] = $this->errors;
        }

        $vars = array('instance', 'type');

        foreach ($vars as $var) {
            if (!empty($this->$var)) {
                $data[$var] = $this->$var;
            }
        }

        return json_encode($data);
    }
}