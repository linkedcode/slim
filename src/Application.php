<?php

namespace Linkedcode\Slim;

use DI\ContainerBuilder;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Slim\App;
use Slim\Exception\HttpNotFoundException;
use Slim\Factory\AppFactory;
use Slim\Psr7\Factory\ResponseFactory;

class Application
{
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
        $app = $container->get(App::class);

        $this->loadMiddlewares($app);
        $this->loadRoutes($app);
        $this->loadListeners($container);
        $this->loadSubscribers($container);

        $app->run();
    }

    private function addDefinitions(array $definitions): void
    {
        $this->containerBuilder->addDefinitions($definitions);
    }

    private function loadRoutes(App $app)
    {
        $file = $this->appDir . '/app/routes.php';

        if (file_exists($file)) {
            $func = require $file;
            $func($app);
        } else {
            die($file . " is required.");
        }

        $app->map(['GET', 'POST', 'PUT', 'DELETE', 'PATCH'], '/{routes:.+}', function ($request, $response) {
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

    private function loadMiddlewares(App $app)
    {
        $middleware = $this->appDir . '/app/middleware.php';

        if (file_exists($middleware)) {
            $func = require $middleware;
            $func($app);
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

        $settings['appDir'] = $this->appDir;

        $this->addDefinitions([
            Settings::class => function () use ($settings) {
                return new Settings($settings);
            },
            'appDir' => function () {
                return $this->appDir;
            }

        ]);
    }

    private function loadDefinitions()
    {
        $this->addDefinitions([
            App::class => function (ContainerInterface $container) {
                return AppFactory::createFromContainer($container);
            },
            ResponseFactoryInterface::class => function (ContainerInterface $container) {
                return $container->get(ResponseFactory::class);
            }
        ]);

        $file = $this->appDir . '/app/definitions.php';

        if (file_exists($file)) {
            $definitions = require $file;
            $this->addDefinitions($definitions);
        }
    }
}