<?php
namespace Osynapsy\Core;

use Osynapsy\Core\Kernel\Loader;
use Osynapsy\Core\Kernel\Router;
use Osynapsy\Core\Kernel\Runner;
use Osynapsy\Core\Network\Request;

class Kernel
{
    const VERSION = '0.3-DEV';
    public $router;
    public $request;
    public $controller;
    public $appController;
    private $loader;    

    public function __construct($fileconf)
    {                
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
        $this->router = new Router($this->request);
        $this->loadRoutes();        
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
        $applicationList = array_keys($this->loader->get('app'));
        foreach($applicationList as $applicationId) {
            $routes = $this->loader->search('route', "app.{$applicationId}");
            foreach ($routes as $route) {
                $id = $route['id'];
                $uri = $route['path'];
                $controller = trim(str_replace(':', '\\', $route['routeValue']));
                $template = $route['template'];
                $this->router->addRoute($id, $uri, $controller, $template, $applicationId, $route);                
            }
        }        
        $this->router->addRoute(
            'OsynapsyAssetsManager',
            '/__assets/osynapsy/?*',
            'Osynapsy\\Core\\Controller\\AssetLoader',
            '',
            'Osynapsy'
        );
    }
    
    public function run($requestRoute = null)
    {
        if (is_null($requestRoute)) {
            $requestRoute = strtok(filter_input(INPUT_SERVER, 'REQUEST_URI'),'?');
        }
        $runner = new Runner($this->request, $this->router, $requestRoute);
        return $runner->run();        
    }            
}
