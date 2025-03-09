<?php

namespace Linkedcode\Slim\ApiProblem;

class ApiProblem
{
    private string $title;
    private int $statusCode;
    private array $errors = [];
    private string $instance = '';
    private string $type = '';
    private string $detail = '';

    public const TYPE_VALIDATION_ERROR = 'validation-error';
    public const TYPE_FORBIDDEN = 'Forbidden';
    public const TYPE_BAD_REQUEST = 'Bad Request';

    private array $titles = [
        self::TYPE_VALIDATION_ERROR => 'Errores de validación',
        self::TYPE_FORBIDDEN => 'Prohibido',
        self::TYPE_BAD_REQUEST => 'Bad request'
    ];

    public function __construct(string $type, int $statusCode)
    {
        $this->type = $type;
        $this->statusCode = $statusCode;

        if (isset($this->titles[$type])) {
            $this->title = $this->titles[$type];
        }
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;
        return $this;
    }

    public function setErrors(array $errors): self
    {
        $this->errors = $errors;
        return $this;
    }

    public function addError(array $error): void
    {
        $this->errors[] = $error;
    }

    public function addError2(string $name, string $reason): static
    {
        $this->errors[] = array(
            'name' => $name,
            'reason' => $reason
        );

        return $this;
    }

    public function setInstance(string $instance): static
    {
        $this->instance = $instance;
        return $this;
    }

    public function setDetail(string $detail): static
    {
        $this->detail = $detail;
        return $this;
    }

    public function getBody(): array
    {
        $data = array(
            'type' => $this->getType(),
            'title' => $this->getTitle(),
            'status' => $this->getStatusCode()
        );

        if (!empty($this->errors)) {
            $data['invalid-params'] = $this->errors;
        }

        $vars = array('instance', 'detail');

        foreach ($vars as $var) {
            if (!empty($this->$var)) {
                $data[$var] = $this->$var;
            }
        }

        return $data;
    }

    public function throw($previous = null): void
    {
        throw new ApiProblemException($this, $previous);
    }

    private function getType(): string
    {
        return $this->type;
    }

    public static function fromApiProblem(string $json): static
    {
        $body = json_decode($json, true);

        $apiProblem = new static($body['type'], $body['status']);

        unset($body['type'], $body['status']);
        
        foreach ($body as $k => $v) {
            if (property_exists($apiProblem, $k) && !empty($v)) {
                $apiProblem->$k = $v;
            }
        }

        return $apiProblem;
    }
}