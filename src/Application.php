<?php

namespace Linkedcode\Slim;

use DI\ContainerBuilder;
use Exception;
use Linkedcode\Slim\Middleware\JwtMiddleware;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\App;
use Slim\Exception\HttpNotFoundException;
use Slim\Factory\AppFactory;

class Application
{
    private App $app;
    private string $appDir;
    private ContainerBuilder $containerBuilder;
    private readonly string $environment;

    private JwtMiddleware $jwtMiddleware;

    private const ENV_DEV = 'development';
    private const ENV_PROD = 'production';

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

        $this->app->run();

        return $this->app;
    }

    private function loadRoutes()
    {
        $routes = $this->appDir . '/app/routes.php';
        if (file_exists($routes)) {
            $func = require $routes;
            $func($this->app, $this->jwtMiddleware);
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

    private function loadMiddlewares()
    {
        $middleware = $this->appDir . '/app/middleware.php';
        if (file_exists($middleware)) {
            $func = require $middleware;
            $func($this->app);
        }

        $this->addCorsMiddleware($this->app);
        $this->addJwtMiddleware($this->appDir);

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

    private function addCorsMiddleware(App $app)
    {
        $app->add(
            function (ServerRequestInterface $request, RequestHandlerInterface $handler) use ($app): ResponseInterface {
                if ($request->getMethod() === 'OPTIONS') {
                    $response = $app->getResponseFactory()->createResponse();
                } else {
                    $response = $handler->handle($request);
                }

                $response = $response
                    ->withHeader('Access-Control-Allow-Credentials', 'true')
                    ->withHeader('Access-Control-Allow-Origin', '*')
                    ->withHeader('Access-Control-Allow-Headers', '*')
                    ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS')
                    ->withHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
                    ->withHeader('Pragma', 'no-cache');

                return $response;
            }
        );
    }

    private function addJwtMiddleware(string $dir)
    {
        $this->jwtMiddleware = new JwtMiddleware($dir);
        //$this->jwtMiddleware->setFake(100);
    }
}