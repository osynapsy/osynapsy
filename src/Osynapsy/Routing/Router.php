<?php

/*
 * This file is part of the Osynapsy package.
 *
 * (c) Pietro Celeste <p.celeste@osynapsy.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Osynapsy\Routing;

/**
 * The router class process all ruotes of the application and search the route
 * that match the request coming from the web
 *
 * @author Pietro Celeste <p.celeste@osynapsy.net>
 */
class Router
{
    private $routes;
    private $matchedRoute;

    //Rispettare l'ordine
    private $patternPlaceholder = [
        '/'  => '\\/',
        //number
        '{i}' => '([\\d]+){1}',
        //Number option
        '{i*}' => '([\\d]*){1}',
        //word
        '{w}'=> '([\\w\-\.,]+){1}',
        //word
        '{w*}' => '([\\w\-\.,]*){1}',
        //all
        '{*}' => '(.*){1}',
        //all after /
        '{?}' => '([^\/]*)',
        //?????
        '{.}' => '([.]+){1}'
    ];

    public function __construct()
    {
        $this->routes = new RouteCollection();
        $this->matchedRoute = new Route('matched');
    }

    /**
     * Get the specified route
     *
     * @param type $key identify of the route
     * @return object
     */
    public function get($key)
    {
        return $this->routes->get($key);
    }

    /**
     * Add route to the collection;
     *
     * @param type $id  identify of the route
     * @param type $url uri of the route
     * @param type $controller controller of the ruote
     * @param type $templateId template of the ruote
     * @param type $application application of the ruote
     * @param array $attributes other attributes of the ruote
     */
    public function addRoute($id, $url, $controller, $templateId, $application, array $attributes = [])
    {
        $this->routes->addRoute($id, $url, $application, $controller, $templateId, $attributes);
    }

    /**
     * Process all the ruote of the collection and return the matched route
     *
     * @param type $uriToMatch ruote request from the web client;
     * @return object matched ruote;
     */
    public function dispatchRoute($uriToMatch)
    {
        $requestRoute = empty($uriToMatch) ? '/' : $uriToMatch;
        $routes = $this->routes->get('routes');
        if (!is_array($routes)) {
            return false;
        }
        //Get current request method;
        $requestMethod = strtolower(filter_input(\INPUT_SERVER, 'REQUEST_METHOD') ?? '');
        foreach($routes as $route) {
            //Check if url accept request http method;
            if (!is_null($route->acceptedMethods) && !in_array($requestMethod, $route->acceptedMethods)) {
               continue;
            }
            //Check if current route match request uri;
            $matchedRoute = $this->matchRoute($route, $requestRoute);
            //If don't match (returned route is false) jump next route;
            if ($matchedRoute === false) {
                continue;
            }
            //If weight is_null requested uri is exactly current uri. Don't search more.
            if (is_null($matchedRoute->weight)) {
                $this->matchedRoute = $matchedRoute;
                break;
            }
            //If weight of previous matched route is bigger of current matchedRoute then continue;
            if (!empty($this->matchedRoute) && $this->matchedRoute->weight > $matchedRoute->weight) {
                continue;
            }
            $this->matchedRoute = $matchedRoute;
        }
        return $this->getRoute();
    }

    /**
     * Process the ruote passed and check if match che request uri.
     *
     * @param type $route
     * @return mixed return false if ruote passed don't match the request uri then return the matched ruote;
     */
    private function matchRoute($route, $requestRoute)
    {
        if (!substr_count($route->uri, '{')) {
            return $route->uri === $requestRoute ? $route : false;
        }
        $patternParams = $this->extractRouteParameterPatterns($route);
        $patternRoute = $this->patternRouteFactory($route, $patternParams, $this->patternPlaceholder);
        $ruoteParameters = $this->extractParametersFromRuote($patternRoute, $requestRoute);
        if (empty($ruoteParameters)) {
            return false;
        }
        array_shift($ruoteParameters);
        $route->parameters = $ruoteParameters;
        $route->weight = count($ruoteParameters);
        return $route;
    }

    protected function extractRouteParameterPatterns($route)
    {
        preg_match_all('/{.+?}/', $route->uri, $output);
        return array_merge(['/' => null] ,  array_flip($output[0]));
    }

    protected function patternRouteFactory($route, $patternParams, $patternPlaceholder)
    {
        /*array_walk(
            $patternParams,
            function(&$value, $key, $placeholder) {
                if (array_key_exists($key, $placeholder)) {
                    $value = $placeholder[$key];
                    return;
                }
                $value = str_replace(['{','}'],['(',')'], $key);
            },
            $patternPlaceholder
        );
        return str_replace(array_keys($patternParams), array_values($patternParams), $route->uri);
         *          */
        $result = [];
        foreach(array_keys($patternParams) as $patternParam) {
            if (array_key_exists($patternParam, $patternPlaceholder)) {
                $result[$patternParam] = $patternPlaceholder[$patternParam];
                continue;
            }
            $result[$patternParam] = str_replace(['{','}'],['(',')'], $patternParam);
        }
        return str_replace(array_keys($result), array_values($result), $route->uri);
    }

    protected function extractParametersFromRuote($patternRoute, $requestRoute)
    {
        preg_match('/^'.$patternRoute.'$/', $requestRoute, $result);
        return $result;
    }

    /**
     * Return the matched uri
     *
     * @return type
     */
    public function getRoute()
    {
        return $this->matchedRoute;
    }
}

/**
 * <?php
$patternPlaceholder = [
        '/'  => '\\/',
        //number
        '{i}' => '([\\d]+){1}',
        //Number option
        '{i*}' => '([\\d]*){1}',
        //word
        '{w}'=> '([\\w\-\.,]+){1}',
        //word
        '{w*}' => '([\\w\-\.,]*){1}',
        //all
        '{*}' => '(.*){1}',
        //all after /
        '{?}' => '([^\/]*)',
        //?????
        '{.}' => '([.]+){1}'
    ];

$uri = '/pippo/{id}/test/{pid:i}';
$output = [];
preg_match_all('/{.+?}/', $uri, $output);
$braceParameters = array_merge(['/' => null] ,  array_flip($output[0]));
var_dump($braceParameters);
$patterns = [];
foreach( $braceParameters as $key => $value) {
	    if(!str_contains( $key, '{')) {
	    	$patterns[] = $key;
	    	continue;
	    }
	    $app = str_replace(['{','}'],'', $key);
    	$pattern = strstr($app, ':') ?: 'w';
    	$name = strstr($app, ':', true) ?: $app;
    	$patterns[$name] = sprintf('(%s)', $pattern);
}
$pattern = str_replace(array_keys($braceParameters),  $patterns, $uri);
var_dump($pattern, $patterns);
 */