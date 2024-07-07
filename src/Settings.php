<?php

namespace Linkedcode\Slim;

use Exception;

class Settings
{
    private readonly array $settings;
    private readonly string $appDir;

    public function __construct(array $settings, string $appDir)
    {
        $this->settings = $settings;
        $this->appDir = $appDir;
    }

    public function get(string $name): mixed
    {
        if (isset($this->settings[$name])) {
            return $this->settings[$name];
        }

        throw new Exception("No existe configuracion [{$name}]");
    }

    public function getPublicKey(): string
    {
        return $this->appDir . '/config/public.key';
    }
}