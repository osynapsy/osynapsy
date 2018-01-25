<?php

/*
 * This file is part of the Osynapsy package.
 *
 * (c) Pietro Celeste <p.celeste@osynapsy.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Osynapsy\Kernel;

class Router
{
    private $routes;
    private $requestRoute;
    private $matchedRoute;
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
    
    public function __construct()
    {
        $this->routes = new RouteCollection();
        $this->matchedRoute = new Route('matched');
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
            $uriDecoded = $this->matchRoute($route->uri);
            if (!$uriDecoded) {
                continue;
            }
            $this->matchedRoute = $route;
            $this->matchedRoute->uri = array_shift($uriDecoded);
            $this->matchedRoute->parameters = $uriDecoded;
        }
        return $this->getRoute();
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
    
    public function getRoute()
    {
        return $this->matchedRoute;
    }
}
