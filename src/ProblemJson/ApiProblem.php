<?php

namespace Linkedcode\Slim\ProblemJson;

class ApiProblem
{
    private string $title;
    private int $statusCode;
    private array $errors = [];
    private string $instance = '';
    private string $type = '';
    private string $detail = '';

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

        $vars = array('instance', 'type', 'detail');

        foreach ($vars as $var) {
            if (!empty($this->$var)) {
                $data[$var] = $this->$var;
            }
        }

        return json_encode($data);
    }

    public static function fromApiProblem(string $json): static
    {
        $body = json_decode($json, true);

        $apiProblem = new static($body['title'], $body['status']);

        unset($body['title'], $body['status']);
        
        foreach ($body as $k => $v) {
            if (property_exists($apiProblem, $k) && !empty($v)) {
                $apiProblem->$k = $v;
            }
        }

        return $apiProblem;
    }
}