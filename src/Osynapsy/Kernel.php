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
use Osynapsy\Kernel\ErrorDispatcher;

/**
 * The Kernel is the core of Osynapsy
 * 
 * It init Http request e translate it in response
 *
 * @author Pietro Celeste <p.celeste@osynapsy.org>
 */
class Kernel
{
    const VERSION = '0.6-DEV';
    
    public $router;
    public $request;
    public $controller;
    public $appController;
    private $loader;    
    private $composer;
    
    /**
     * Kernel costructor
     * 
     * @param string $instanceConfigurationFile path of the instance configuration file
     * @param object $composer Instance of composer loader
     */
    public function __construct($instanceConfigurationFile, $composer = null)
    {
        $this->composer = $composer;
        $this->loader = new Loader($instanceConfigurationFile);
        $this->request = new Request(
            $_GET, 
            $_POST,
            [],
            $_COOKIE,
            $_FILES,
            $_SERVER
        );
        $this->request->set(
            'app.parameters',
            $this->loadConfig('parameter', 'name', 'value')
        );
        $this->request->set(
            'env',
            $this->loader->get()
        );        
        $this->request->set(
            'app.layouts',
            $this->loadConfig('layout', 'name', 'path')
        );
        $this->request->set(
            'observers',
            $this->loadConfig('observer', '@value', 'subject')
        );
        $this->request->set(
            'listeners',
            $this->loadConfig('listener', '@value', 'event')
        );
    }
    
    
    public function getRequest()
    {
        return $this->request;
    }
    
    public function getVersion()
    {
        return self::VERSION;
    }
    
    private function loadConfig($key, $name, $value)
    {
        $array = $this->loader->search($key);
        $result = [];
        foreach($array as $rec) {
            $result[$rec[$name]] = $rec[$value];
        }
        return $result;
    }
    
    /**
     * Load in router object all route of application present in config file
     */
    private function loadRoutes()
    {        
        $this->router = new Router($this->request);
        $this->router->addRoute(
            'OsynapsyAssetsManager',
            '/assets/osynapsy/'.self::VERSION.'/{*}',
            'Osynapsy\\Assets\\Loader',
            '',
            'Osynapsy'
        );
        $applications = $this->loader->get('app');
        if (empty($applications)) {
            throw new KernelException('No app configuration found', 1001);
        }
        foreach (array_keys($applications) as $applicationId) {
            $routes = $this->loader->search('route', "app.{$applicationId}");
            foreach ($routes as $route) {
                if (!isset($route['path'])) {
                    continue;
                }
                $id = isset($route['id']) ? $route['id'] : uniqid();
                $uri = $route['path'];
                $controller = $route['@value'];
                $template = !empty($route['template']) ? $this->request->get('app.layouts.'.$route['template']) : '';
                $this->router->addRoute($id, $uri, $controller, $template, $applicationId, $route);                
            }
        }        
    }
    
    /**
     * Run process to get response starting to request uri
     * 
     * @param string $requestUri is Uri requested from 
     * @return string 
     */
    public function run($requestUri = null)
    {
        if (is_null($requestUri)) {
            $requestUri = strtok(filter_input(INPUT_SERVER, 'REQUEST_URI'), '?');
        }
        try {
            $this->loadRoutes();
            $route = $this->router->dispatchRoute($requestUri);
            $this->getRequest()->set('page.route', $route);            
            return $this->runApplication($route);
        } catch (\Exception $exception) {
            $errorDispatcher = new ErrorDispatcher($this->request);
            return $errorDispatcher->dispatchException($exception);
        } catch (\Error $error) {
            $errorDispatcher = new ErrorDispatcher($error, $this->request);
            return $errorDispatcher->dispatchError($error);
        }
    }
    
    public function runApplication($route)
    {
        if (!$route->controller) {
            throw new KernelException(
                'No route to destination ('.$this->request->get('server.REQUEST_URI').')', 
                404
            );
        }
        $reqApp = $this->request->get("env.app.{$route->application}.controller");
        //If isn't configured an app controller for current instance load default 
        //App controller
        $applicationClass = empty($reqApp) ? '\\Osynapsy\\Mvc\\Application' : str_replace(':', '\\',$reqApp);        
        //If app has applicationController instance it before recall route controller;        
        $application = new $applicationClass($route, $this->request);
        if (!$application->run()) {
            throw new \Osynapsy\Mvc\ApplicationException('Access denied','501');
        }
        return (string) $application->runAction();
    }
}
