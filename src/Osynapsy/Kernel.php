<?php
namespace Osynapsy;

use Osynapsy\Http\Request;
use Osynapsy\Kernel\Loader;
use Osynapsy\Kernel\Route;
use Osynapsy\Kernel\Router;
use Osynapsy\Kernel\Runner;

class Kernel
{
    const VERSION = '0.4.1-DEV';
    
    public $router;
    public $request;
    public $controller;
    public $appController;
    private $loader;    
    private $composer;
    
    public function __construct($fileconf, $composer = '')
    {                
        $this->composer = $composer;
        $this->loader = new Loader($fileconf);
        $this->request = new Request($_GET, $_POST, array(), $_COOKIE, $_FILES, $_SERVER);
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
            $this->loadConfig('observer', 'observerValue', 'subject')
        );
        $this->request->set(
            'listeners',
            $this->loadConfig('listener', 'listenerValue', 'event')
        );                
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
     * 
     * 
     */
    private function loadRoutes()
    {        
        $this->router = new Router($this->request);
        $this->router->addRoute(
            'OsynapsyAssetsManager',
            '/__assets/osynapsy/?*',
            'Osynapsy\\Assets\\Loader',
            '',
            'Osynapsy'
        );
        $applications = $this->loader->get('app');
        if (empty($applications)) {
            throw new KernelException('No app configuration found');
        }
        foreach(array_keys($applications) as $applicationId) {
            $routes = $this->loader->search('route', "app.{$applicationId}");
            foreach ($routes as $route) {
                $id = $route['id'];
                $uri = $route['path'];
                $controller = $route['routeValue'];
                $template = !empty($route['template']) ? $this->request->get('app.layouts.'.$route['template']) : '';
                $this->router->addRoute($id, $uri, $controller, $template, $applicationId, $route);                
            }
        }        
    }
    
    public function run($requestUri = null)
    {
        if (is_null($requestUri)) {
            $requestUri = strtok(filter_input(INPUT_SERVER, 'REQUEST_URI'),'?');
        }
        $this->loadRoutes();
        return $this->followRoute(
            $this->router->dispatchRoute($requestUri)
        );
    }
    
    public function followRoute(Route $route)
    {
        $this->request->set('page', $route);
        $runner = new Runner($this->request, $route);
        return $runner->run();  
    }
}
