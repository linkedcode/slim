<?php

namespace Linkedcode\Slim;

use Exception;

class Settings
{
    private readonly array $settings;
    
    private array $drivers = [
        'pdo_mysql' => 'mysql'
    ];

    public function __construct(array $settings)
    {
        $this->settings = $settings;
    }

    public function get(string $name, mixed $default = null): mixed
    {
        if (isset($this->settings[$name])) {
            return $this->settings[$name];
        }

        if (stripos($name, '.')) {
            $p = explode('.', $name);

            if (count($p) == 2) {
                if (isset($this->settings[$p[0]][$p[1]])) {
                    return $this->settings[$p[0]][$p[1]];
                }
            } else if (count($p) == 3) {
                if (isset($this->settings[$p[0]][$p[1]][$p[2]])) {
                    return $this->settings[$p[0]][$p[1]][$p[2]];
                }
            }
        }

        if ($default !== null) {
            return $default;
        }

        throw new Exception("No existe configuracion [{$name}]");
    }

    public function getPublicKey(): string
    {
        return $this->getAppDir() . '/config/public.key';
    }

    public function getAppDir(): string
    {
        return $this->get('appDir');
    }

    /**
     * 'mysql:host=localhost;dbname=testdb';
     */
    public function getDsn(): string
    {
        $db = $this->get('db');

        $dsn = sprintf(
            "%s:host=%s;dbname=%s",
            $this->getDbDriver($db['driver']),
            $db['host'],
            $db['dbname']
        );

        return $dsn;
    }

    private function getDbDriver(string $driver): string
    {
        if (isset($this->drivers[$driver])) {
            return $this->drivers[$driver];
        }

        throw new Exception(
            sprintf("Driver [%s] not found.", $driver)
        );
    }
}