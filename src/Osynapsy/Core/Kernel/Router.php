<?php
namespace Osynapsy\Core\Kernel;

use Osynapsy\Core\Network\Request;

class Router
{
    public $request;
    private $routes;
    private $requestRoute;
    //Rispettare l'ordine
    private $patternPlaceholder = array(
        '?i' => '([\\d]+){1}', 
        '?I' => '([\\d]*){1}',
        '?.' => '([.]+){1}',
        '?w' => '([\\w-,]+){1}', 
        '?*'  => '(.*){1}',
        '?' => '([^\/]*)',
        '/'  => '\\/'
    );
    
    public function __construct(Request &$request)
    {
        $this->request = $request;
        $this->routes = new RouteCollection();        
    }
    
    private function matchRoute($url)
    {
        $output = [];
        switch (substr_count($url, '?')) {
            case 0:
                if ($url === $this->requestRoute) {
                    $output[] = $url;  
                }
                break;
            default:
                $pattern = str_replace(
                    array_keys($this->patternPlaceholder),
                    array_values($this->patternPlaceholder),
                    $url
                );
                preg_match('|^'.$pattern.'$|', $this->requestRoute, $output);
                break;
        }
        
        return empty($output) ? false : $output;
    }

    public function get($key)
    {
        return $this->routes->get($key);
    }
    
    public function addRoute($id, $url, $controller, $templateId, $application, $attributes=array())
    {    
        $this->routes->addRoute($id, $url, $application, $controller, $templateId, $attributes);        
    }
    
    public function dispatchRoute($uriToMatch)
    {
        $this->requestRoute = empty($uriToMatch) ? '/' : $uriToMatch;
        $routes = $this->routes->get('routes');
        if (!is_array($routes)) {
            return false;
        }
        foreach($routes as $route) {
            $uriDecoded = $this->matchRoute($route['path']);
            if (!$uriDecoded) {
                continue;
            }
            $this->routes->set('current', $route); 
            $this->routes->set('current.url', array_shift($uriDecoded));
            $this->routes->set('current.parameters', $uriDecoded);
            $this->request->set('page', $this->routes->get('current'));
            return true;
        }
        return false;
    }
    
    public function getRoute($key='')
    {
        if (!empty($key)){
            $key = '.'.$key;
        }
        return $this->get('current'.$key);
    }
}
