<?php

namespace Linkedcode\Slim;

use DI\ContainerBuilder;
use Exception;
use Slim\App;

class Application
{
    private App $app;
    private string $appDir;

    public function __construct($appDir)
    {
        $this->appDir = $appDir;
        $container = $this->createContainer();

        //$listeners = require __DIR__ . '/../app/listeners.php';
        //$listeners($container);

        $this->app = $container->get(App::class);

        $this->loadMiddlewares();
        $this->loadRoutes();
    }

    public function run()
    {
        $this->app->run();
    }

    private function loadRoutes()
    {
        $routes = $this->appDir . '/app/routes.php';
        if (file_exists($routes)) {
            $func = require_once $routes;
            $func($this->app);
        }
    }

    private function loadMiddlewares()
    {
        $middleware = $this->appDir . '/app/middleware.php';
        if (file_exists($middleware)) {
            $func = require_once $middleware;
            $func($this->app);
        }
    }

    private function createContainer()
    {
        $containerBuilder = new ContainerBuilder();

        $this->loadDefinitions($containerBuilder);
        
        return $containerBuilder->build();
    }

    private function loadDefinitions(ContainerBuilder $containerBuilder)
    {
        $definitions = $this->appDir . '/app/definitions.php';
        if (file_exists($definitions)) {
            $func = require_once $definitions;
            $func($containerBuilder);
        } else {
            throw new Exception($definitions . " is required.");
        }

        //$db = require __DIR__ . '/../app/db.php';
        //$db($containerBuilder);
    }
}