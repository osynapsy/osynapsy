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
        '/'  => '\\/',
        //number
        '{i}' => '([\\d]+){1}',
        //Number option
        '{i*}' => '([\\d]*){1}',
        //word
        '{w}' => '([\\w-,]+){1}',
        //all
        '{*}' => '(.*){1}',
        //all after /
        '{?}' => '([^\/]*)'
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
        if (substr_count($url, '{')) {
            return $this->matchRouteNew($url);
        }
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
    
    private function matchRouteNew($url)
    {
        if (!substr_count($url, '{')) {
            return $url === $this->requestRoute ? [$url] : false;  
        }
        $output = $result = [];        
        preg_match_all('/{.+?}/', $url, $output);
        $braceParameters = array_merge(
            ['/' => null] , 
            empty($output) ? [] : array_flip($output[0])
        );        
        array_walk(
            $braceParameters, 
            function(&$value, $key, $placeholder) {            
                if (array_key_exists($key, $placeholder)) {
                    $value = $placeholder[$key];               
                } else {
                    $value = str_replace(['{','}'],['(',')'], $key);
                }
            }, 
            $this->patternPlaceholder
        );        
        $pattern = str_replace(array_keys($braceParameters), array_values($braceParameters), $url);         
        preg_match('/'.$pattern.'/', $this->requestRoute, $result);        
        return empty($result) ? false : $result;
    }
    
    public function getRoute()
    {
        return $this->matchedRoute;
    }    
}
