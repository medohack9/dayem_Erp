<?php

namespace App\Core;

class App
{
    private array $config = [];

    public function __construct()
    {
        $this->config = require ROOT_PATH . '/config/app.php';

        if ($this->config['base_path'] === null) {
            $scriptDir = dirname($_SERVER['SCRIPT_NAME'] ?? '');
            $this->config['base_path'] = $scriptDir === '/' || $scriptDir === '\\' ? '' : rtrim($scriptDir, '/\\');
        }

        $GLOBALS['app_config'] = $this->config;

        date_default_timezone_set($this->config['timezone']);

        $this->registerAutoloader();
        $this->initErrorHandler();
        $this->initSession();
        $this->dispatch();
    }

    private function registerAutoloader(): void
    {
        spl_autoload_register(function (string $class) {
            $prefix = 'App\\';
            $baseDir = ROOT_PATH . '/app/';

            if (strncmp($prefix, $class, strlen($prefix)) !== 0) {
                return;
            }

            $relativeClass = substr($class, strlen($prefix));
            $file = $baseDir . strtolower(str_replace('\\', '/', $relativeClass)) . '.php';

            if (file_exists($file)) {
                require $file;
            }
        });
    }

    private function initErrorHandler(): void
    {
        new ErrorHandler($this->config['environment']);
    }

    private function initSession(): void
    {
        new Session($this->config['session_timeout']);
    }

    private function dispatch(): void
    {
        $router = new Router();
        $routes = require ROOT_PATH . '/config/routes.php';
        $router->addRoutes($routes);
        $router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
    }
}