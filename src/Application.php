<?php

namespace Linkedcode\Slim;

use DI\ContainerBuilder;
use Exception;
use Psr\Container\ContainerInterface;
use Slim\App;
use Slim\Factory\AppFactory;

class Application
{
    private App $app;
    private string $appDir;
    private ContainerBuilder $containerBuilder;
    private readonly string $environment;

    private const ENV_DEV = 'development';
    private const ENV_PROD = 'production';

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
        $this->loadListeners($container);

        $this->app->run();

        return $this->app;
    }

    private function loadRoutes()
    {
        $routes = $this->appDir . '/app/routes.php';
        if (file_exists($routes)) {
            $func = require $routes;
            $func($this->app);
        }
    }

    private function loadListeners(ContainerInterface $container)
    {
        $listeners = $this->appDir . '/app/listeners.php';
        if (file_exists($listeners)) {
            $func = require $listeners;
            $func($container);
        }
    }

    private function loadMiddlewares()
    {
        $middleware = $this->appDir . '/app/middleware.php';
        if (file_exists($middleware)) {
            $func = require $middleware;
            $func($this->app);
        }
    }

    private function loadSettings()
    {
        $file = $this->appDir . '/config/settings.php';
        $base = require $file;

        $fileProd = $this->appDir . '/config/settings.prod.php';
        if (file_exists($fileProd)) {
            $settingsProd = require_once $fileProd;
            $settings = array_merge($base, $settingsProd);
            $this->environment = self::ENV_PROD;
        } else {
            ini_set('display_errors', 1);
            $fileDev = $this->appDir . '/config/settings.dev.php';
            if (file_exists($fileDev)) {
                $settingsDev = require_once $fileDev;
                $settings = array_merge($base, $settingsDev);
                $this->environment = self::ENV_DEV;
            }
        }
        
        $defs = array(
            Settings::class => function(ContainerInterface $container) {
                return new Settings($container->get('settings'), $this->appDir);
            },
            'settings' => function() use ($settings) {
                return $settings;
            }
        );

        $this->containerBuilder->addDefinitions($defs);
    }

    private function loadDefinitions()
    {
        $this->containerBuilder->addDefinitions([
            App::class => function (ContainerInterface $container) {
                return AppFactory::createFromContainer($container);
            }
        ]);

        $file = $this->appDir . '/app/definitions.php';

        if (file_exists($file)) {
            $definitions = require $file;
            $this->containerBuilder->addDefinitions($definitions);
        } else {
            //throw new Exception($file . " is required.");
        }
    }
}