<?php
namespace Osynapsy\Core\Kernel;

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
    
    public function __construct($requestRoute, Request &$request)
    {
        $this->requestRoute = empty($requestRoute) ? '/' : $requestRoute;
        $this->request = $request;
        $this->routes = new RouteCollection();        
    }
    
    private function isCurrentRoute($url, $ctl, $tpl, $app, $attr=null)
    {
        $out = array();
        switch (substr_count($url, '?')) {
            case 0:
                if ($url === $this->requestRoute) {
                    $out[] = $url;  
                }
                break;
            default:
                $pattern = str_replace(
                    array_keys($this->patternPlaceholder),
                    array_values($this->patternPlaceholder),
                    $url
                );
                preg_match('|^'.$pattern.'$|', $this->requestRoute, $out);
                break;
        }
        
        if (empty($out)){
            return;
        }
        
        $this->routes->set('current.url', array_shift($out));
        $this->routes->set('current.controller', $ctl); 
        $this->routes->set('current.templateId', $tpl);
        $this->routes->set('current.application', $app);
        $this->routes->set('current.parameters', $out);
        $this->routes->set('current.attributes', $attr);
        $this->request->set('page', $this->routes->get('current'));
    }

    public function get($key)
    {
        return $this->routes->get($key);
    }
    
    public function addRoute($id, $url, $controller, $templateId, $application, $attributes=array())
    {    
        $this->routes->addRoute($id, $url, $controller, $templateId);
        $this->isCurrentRoute($url, $controller, $templateId, $application, $attributes);        
    }
    
    public function getRoute($key='')
    {
        if (!empty($key)){
            $key = '.'.$key;
        }
        return $this->get('current'.$key);
    }
}
