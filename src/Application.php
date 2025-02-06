<?php

namespace Linkedcode\Slim;

use DI\ContainerBuilder;
use Psr\Container\ContainerInterface;
use Slim\App;
use Slim\Exception\HttpNotFoundException;
use Slim\Factory\AppFactory;

class Application
{
    private App $app;
    private string $appDir;
    private ContainerBuilder $containerBuilder;

    public function __construct(string $appDir)
    {
        $this->appDir = $appDir;
        $this->containerBuilder = new ContainerBuilder();
    }

    public function run()
    {
        $this->loadDefinitions();
        $this->loadSettings();

        $container = $this->containerBuilder->build();

        $this->app = $container->get(App::class);

        $this->loadMiddlewares();
        $this->loadRoutes();
        $this->loadListeners($container);
        $this->loadSubscribers($container);

        $this->app->run();

        return $this->app;
    }

    public function addDefinitions(array $definitions): void
    {
        $this->containerBuilder->addDefinitions($definitions);
    }

    private function loadRoutes()
    {
        $file = $this->appDir . '/app/routes.php';

        if (file_exists($file)) {
            $func = require $file;
            $func($this->app);
        } else {
            die($file . " is required.");
        }

        /**
         * Catch-all route to serve a 404 Not Found page if none of the routes match
         * NOTE: make sure this route is defined last
         */
        $this->app->map(['GET', 'POST', 'PUT', 'DELETE', 'PATCH'], '/{routes:.+}', function ($request, $response) {
            throw new HttpNotFoundException($request);
        });
    }

    private function loadListeners(ContainerInterface $container)
    {
        $listeners = $this->appDir . '/app/listeners.php';
        if (file_exists($listeners)) {
            $func = require $listeners;
            $func($container);
        }
    }

    private function loadSubscribers(ContainerInterface $container)
    {
        $subscribers = $this->appDir . '/app/subscribers.php';
        if (file_exists($subscribers)) {
            $func = require $subscribers;
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
        $settings = require $file;

        $fileProd = $this->appDir . '/config/settings.prod.php';
        if (file_exists($fileProd)) {
            $prod = require_once $fileProd;
            $settings = array_merge_recursive($settings, $prod);
        } else {
            $fileDev = $this->appDir . '/config/settings.dev.php';
            if (file_exists($fileDev)) {
                $dev = require_once $fileDev;
                $settings = array_merge_recursive($settings, $dev);
            }
        }
        
        $defs = array(
            Settings::class => function() use ($settings) {
                return new Settings($settings, $this->appDir);
            }
        );

        $this->addDefinitions($defs);
    }

    private function loadDefinitions()
    {
        $this->addDefinitions([
            App::class => function (ContainerInterface $container) {
                return AppFactory::createFromContainer($container);
            },
            'appDir' => function() {
                return $this->appDir;
            }
        ]);

        $file = $this->appDir . '/app/definitions.php';

        if (file_exists($file)) {
            $definitions = require $file;
            $this->addDefinitions($definitions);
        }
    }
}