<?php

/*
 * This file is part of the Osynapsy package.
 *
 * (c) Pietro Celeste <p.celeste@osynapsy.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Osynapsy;

use Osynapsy\Http\Request;
use Osynapsy\Http\Emitter;
use Osynapsy\Http\Response\ResponseInterface;
use Osynapsy\Psr\Http\ServerRequest as PsrRequest;
use Osynapsy\Kernel\ConfigLoader;
use Osynapsy\Routing\Router;
use Osynapsy\Kernel\KernelException;
use Osynapsy\Kernel\Error\Dispatcher as ErrorDispatcher;
use Osynapsy\Application\BaseApplication;
use Osynapsy\Helper\AssetLoader\AssetLoader;

/**
* The Kernel is the core of Osynapsy
*
* It init Http request e translate it in response
*
* @author Pietro Celeste <p.celeste@osynapsy.net>
*/

class Kernel
{
    const VERSION = '0.9.11-DEV';
    const ACTION_HEADER_KEY = 'HTTP_X_OSYNAPSY_ACTION';
    const DEFAULT_APP_CONTROLLER = BaseApplication::class;
    const DEFAULT_ASSET_CONTROLLER = AssetLoader::class;

    public $router;
    public static $request;
    public $psrRequest;
    public $composer;
    private $loader;
    public $route;

    /**
     * Kernel costructor
     *
     * @param string $instanceConfigurationFile path of the instance configuration file
     * @param object $composer Instance of composer loader
     */
    public function __construct($instanceConfigurationFile, $composer = null)
    {
        $this->loader = new ConfigLoader($instanceConfigurationFile);
        $this->composer = $composer;
    }

    /**
     * Run process to get response starting to request uri
     *
     * @return string
     */
    public function run()
    {
        try {
            self::$request = $this->requestFactory();
            $this->psrRequest = $this->psr7RequestFactory();
            $requestUri = $this->requestUriFactory();
            $router = $this->routerFactory($this->getRequest(), $requestUri);
            $applications = $this->getLoader()->get('app');
            if (empty($applications)) {
                throw $this->raiseException(1001, 'No app configuration found');
            }
            $this->loadApplicationRoutes($router, $applications);
            $this->route = $this->findRequestRoute($router, $requestUri);
            $this->validateRouteController($this->route);
            $response = $this->runApplication($this->route, $this->getRequest());
            return (new Emitter($response))->emit();
        } catch (\Exception $exception) {
            $errorDispatcher = new ErrorDispatcher($this->getRequest());
            return $errorDispatcher->dispatchException($exception);
        } catch (\Error $error) {
            $errorDispatcher = new ErrorDispatcher($this->getRequest());
            return $errorDispatcher->dispatchError($error);
        }
    }

    public function requestFactory()
    {
        $request = new Request($_GET, $_POST, [], $_COOKIE, $_FILES, $_SERVER);
        $request->set('app.parameters', $this->loadConfig('parameter', 'name', 'value'));
        $request->set('env', $this->getLoader()->get());
        $request->set('app.templates', $this->loadConfig('layout', 'name'));
        $request->set('observers', $this->loadConfig('observer', '@value', 'subject'));
        $request->set('listeners', $this->loadConfig('listener', '@value', 'event'));
        return $request;
    }

    private  function psr7RequestFactory()
    {
        $psrRequest = PsrRequest::fromGlobals();
        self::$request->set('psr7Request', $psrRequest);
        return $psrRequest;
    }

    private function requestUriFactory()
    {
        return $this->getPsrRequest()->getUri()->getPath();
    }

    private function loadConfig($dictionaryDataPath, $fielId, $fieldValue = null)
    {
        $rawdata = $this->getLoader()->search($dictionaryDataPath);
        $result = [];
        foreach($rawdata as $rec) {
            $result[$rec[$fielId]] = is_null($fieldValue) ? $rec : $rec[$fieldValue];
        }
        return $result;
    }

    private function routerFactory($request)
    {
        $router = new Router($request);
        $router->addRoute('OsynapsyAssetsManager', '/assets/{path:*}', self::DEFAULT_ASSET_CONTROLLER, '', 'Osynapsy');
        return $router;
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
        self::$request->set('route', $route);
        return $route;
    }

    public function runApplication($route, $request) : ResponseInterface
    {
        $reqApp = $request->get(sprintf("env.app.%s.controller", $route->application));
        //If isn't configured an app controller for current instance load default App controller
        $hypervisorClass = empty($reqApp) ? self::DEFAULT_APP_CONTROLLER : str_replace(':', '\\', $reqApp);
        $hypervisor = new $hypervisorClass($route, $request);
        $hypervisor->setComposer($this->composer);
        return $hypervisor->execute();
    }

    private function validateRouteController($route)
    {
        if (empty($route) || !$route->controller) {
            throw $this->raiseException(404, "Route not found", sprintf(
                'THE REQUEST PAGE NOT EXIST ON THIS SERVER <br><br> %s',
                $this->getRequest()->get('server.REQUEST_URI') . print_r($route, true)
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

    public function getPsrRequest()
    {
        return $this->psrRequest;
    }

    public function getRequest()
    {
        return self::$request;
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
