<?php

namespace Linkedcode\Slim;

use DI\ContainerBuilder;
use Exception;
use Psr\Container\ContainerInterface;
use Slim\App;

class Application
{
    private App $app;
    private string $appDir;
    private ContainerBuilder $containerBuilder;

    public function __construct(string $appDir)
    {
        $this->appDir = $appDir;
        $this->containerBuilder = new ContainerBuilder();

        $this->loadDefinitions();
        $this->loadSettings();
    }

    public function run()
    {
        $container = $this->containerBuilder->build();

        $this->app = $container->get(App::class);

        $this->loadMiddlewares();
        $this->loadRoutes();

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

    private function loadSettings()
    {
        $settings = $this->appDir . '/config/settings.php';
        
        if (!file_exists($settings)) {
            return;
        }

        $defs = array(
            Settings::class => function(ContainerInterface $container) {
                return new Settings($container->get('settings'));
            },
            'settings' => function() use ($settings) {
                return require_once $settings;
            }
        );

        $this->containerBuilder->addDefinitions($defs);
    }

    private function loadDefinitions()
    {
        $definitions = $this->appDir . '/app/definitions.php';
        if (file_exists($definitions)) {
            $func = require_once $definitions;
            $func($this->containerBuilder);
        } else {
            throw new Exception($definitions . " is required.");
        }

        //$db = require __DIR__ . '/../app/db.php';
        //$db($containerBuilder);
    }
}