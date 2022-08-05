<?php

/*
 * This file is part of the Osynapsy package.
 *
 * (c) Pietro Celeste <p.celeste@osynapsy.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Osynapsy;

use Osynapsy\Http\Request;
use Osynapsy\Kernel\Loader;
use Osynapsy\Kernel\Router;
use Osynapsy\Kernel\KernelException;
use Osynapsy\Kernel\Error\Dispatcher as ErrorDispatcher;

/**
 * The Kernel is the core of Osynapsy
 *
 * It init Http request e translate it in response
 *
 * @author Pietro Celeste <p.celeste@osynapsy.org>
 */
class Kernel
{
    const VERSION = '0.8.5-DEV';
    const DEFAULT_APP_CONTROLLER = '\\Osynapsy\\Mvc\\Application\\BaseApplication';
    const DEFAULT_ASSET_CONTROLLER = 'Osynapsy\\Assets\\Loader';

    public $router;
    public $request;
    private $loader;

    /**
     * Kernel costructor
     *
     * @param string $instanceConfigurationFile path of the instance configuration file
     * @param object $composer Instance of composer loader
     */
    public function __construct($instanceConfigurationFile)
    {
        $this->loader = new Loader($instanceConfigurationFile);
    }

    /**
     * Run process to get response starting to request uri
     *
     * @return string
     */
    public function run()
    {
        try {
            $this->request = $this->requestFactory();
            $requestUri = $this->requestUriFactory();
            $router = $this->routerFactory($this->getRequest(), $requestUri);
            $applications = $this->getLoader()->get('app');
            if (empty($applications)) {
                throw $this->raiseException(1001, 'No app configuration found');
            }
            $this->loadApplicationRoutes($router, $applications);
            $route = $this->findRequestRoute($router, $requestUri);
            $this->validateRouteController($route);
            return $this->runApplication($route, $this->getRequest());
        } catch (\Exception $exception) {
            $errorDispatcher = new ErrorDispatcher($this->getRequest());
            return $errorDispatcher->dispatchException($exception);
        } catch (\Error $error) {
            $errorDispatcher = new ErrorDispatcher($this->getRequest());
            return $errorDispatcher->dispatchError($error);
        }
    }

    private function requestFactory()
    {
        $request = new Request($_GET, $_POST, [], $_COOKIE, $_FILES, $_SERVER);
        $request->set('app.parameters', $this->loadConfig('parameter', 'name', 'value'));
        $request->set('env', $this->getLoader()->get());
        $request->set('app.template', $this->loadConfig('layout', 'name', 'path'));
        $request->set('observers', $this->loadConfig('observer', '@value', 'subject'));
        $request->set('listeners', $this->loadConfig('listener', '@value', 'event'));
        return $request;
    }

    private function loadConfig($key, $name, $value)
    {
        $array = $this->getLoader()->search($key);
        $result = [];
        foreach($array as $rec) {
            $result[$rec[$name]] = $rec[$value];
        }
        return $result;
    }

    private function routerFactory($request)
    {
        $router = new Router($request);
        $router->addRoute('OsynapsyAssetsManager', '/assets/osynapsy/'.self::VERSION.'/{*}', self::DEFAULT_ASSET_CONTROLLER, '', 'Osynapsy');
        return $router;
    }

    private function requestUriFactory()
    {
        return strtok(filter_input(INPUT_SERVER, 'REQUEST_URI'), '?');
    }

    /**
     * Load in router object all route of application present in config file
     */
    private function loadApplicationRoutes($router, $applications)
    {
        foreach (array_keys($applications) as $applicationId) {
            $routes = $this->loader->search('route', "app.{$applicationId}");
            foreach ($routes as $route) {
                if (!isset($route['path'])) {
                    continue;
                }
                $id = isset($route['id']) ? $route['id'] : uniqid();
                $uri = $route['path'];
                $controller = $route['@value'];
                $template = $route['template'] ?? null;
                $router->addRoute($id, $uri, $controller, $template, $applicationId, $route);
            }
        }
    }

    private function findRequestRoute($router, $requestUri)
    {
        $route = $router->dispatchRoute($requestUri);
        $this->getRequest()->set('page.route', $route);
        return $route;
    }

    private function runApplication($route, $request)
    {
        $reqApp = $request->get(sprintf("env.app.%s.controller", $route->application));
        //If isn't configured an app controller for current instance load default App controller
        $applicationClass = empty($reqApp) ? self::DEFAULT_APP_CONTROLLER : str_replace(':', '\\', $reqApp);
        $application = new $applicationClass($route, $request);
        return (string) $application->execute();
    }

    private function validateRouteController($route)
    {
        if (empty($route)) {
            throw $this->raiseException(404, "Page not found", sprintf(
                'THE REQUEST PAGE NOT EXIST ON THIS SERVER <br><br> %s',
                $this->request->get('server.REQUEST_URI')
            ));
        }
    }

    protected function raiseException($code, $message, $submessage = '')
    {
        $exception = new KernelException($message, $code);
        if (!empty($submessage)) {
            $exception->setInfoMessage($submessage);
        }
        return $exception;
    }

    public function getLoader()
    {
        return $this->loader;
    }

    public function getRequest()
    {
        return $this->request;
    }

    public function getRouter()
    {
        return $this->router;
    }

    public function getVersion()
    {
        return self::VERSION;
    }
}
