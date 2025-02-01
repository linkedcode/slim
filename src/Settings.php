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

        if (stripos($name, '.')) {
            $p = explode('.', $name);

            if (isset($this->settings[$p[0]][$p[1]])) {
                return $this->settings[$p[0]][$p[1]];
            }
        }

        throw new Exception("No existe configuracion [{$name}]");
    }

    public function getPublicKey(): string
    {
        return $this->appDir . '/config/public.key';
    }

    /**
     * 'mysql:host=localhost;dbname=testdb';
     */
    
    public function getDsn(): string
    {
        $db = $this->get('db');

        $drivers = [
            'pdo_mysql' => 'mysql'
        ];

        $driver = $drivers[$db['driver']];

        $dsn = sprintf("%s:host=%s;dbname=%s", $driver, $db['host'], $db['dbname']);

        return $dsn;
    }
}