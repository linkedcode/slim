<?php

namespace Linkedcode\Slim;

use Exception;

class Settings
{
    private readonly array $settings;

    public function __construct(array $settings)
    {
        $this->settings = $settings;
    }

    public function get(string $name): mixed
    {
        if (isset($this->settings[$name])) {
            return $this->settings[$name];
        }

        throw new Exception("No existe configuracion [{$name}]");
    }
}