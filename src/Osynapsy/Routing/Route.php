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
 * Description of Route
 *
 * @author Peter
 */
class Route
{
    //Rispettare l'ordine
    const PATTERN_MAP = [
        '/'  => '\\/',
        //number
        'i' => '([\\d]+){1}',
        //Number option
        'i*' => '([\\d]*){1}',
        //word
        'w'=> '([\\w\-\.,]+){1}',
        //word
        'w*' => '([\\w\-\.,]*){1}',
        //all
        '*' => '(.*){1}',
        //all after /
        '?' => '([^\/]*)',
        //?????
        '.' => '([.]+){1}'
    ];
    
    private $route = [
        'id' => null,
        'uri' => null,
        'application' => null,
        'controller' => null,
        'template' => null,
        'weight' => null,
        'acceptedMethods' => null,        
    ];

    protected $parameters = [];
    
    public function __construct($id = '', $uri = '', $application = '', $controller = '', $template = '', array $attributes = [])
    {
        $this->id = empty($id) ? sha1($uri) : $id;
        $this->uri = $uri;
        $this->application = trim($application);
        $this->setController($controller);
        $this->template = $template;      
        $this->route += $attributes;
        $this->setAcceptedMethods($this->methods);
        $this->initParameters($uri);
    }
    
    protected function initParameters($uri)
    {
        //   ----- {(.+?)(}|:(.*?)}) ---- Prende i segnaposti in 0 i nome delle variabili in 1 e le espressioni regolari in 3 /order/{sectionid:i}/{id}/{ciao:pippo|pluto}
        //preg_match_all('/{.+?}/', $route->uri, $output);
        preg_match_all('/{(.+?)(:([^}]*))?}/', $uri, $output);
        if (empty($output) || empty($output[0])) {
            return;
        }
        $placeholders = $output[0];
        $parameterIds = $output[1];
        $parameterRules = $output[3];
        foreach($placeholders as $i => $placeholder) {
            $parameterId = $parameterIds[$i];            
            $ruleId = $parameterRules[$i] ?: '?';
            $parameterRule = self::PATTERN_MAP[$ruleId] ?? $ruleId;
            $this->parameters[$parameterId] = [
                'id' => $parameterId, 
                'rule' => $ruleId, 
                'placeholder' => $placeholder, 
                'pattern' => $parameterRule,
                'value' => null
            ];
        }          
    }
    
    public function getParameter($key)
    {
        if (is_int($key)) {
            return array_column($this->parameters, 'value')[$key] ?? null;
        }
        return $this->parameters[$key]['value'] ?? null;
    }
    
    public function getParameters()
    {
        return $this->parameters;
    }

    public function getUrl(array $params = [])
    {
        $output = $result = [];
        preg_match_all('/{.+?}/', $this->uri, $output);
        if (count($output[0]) > count($params)) {
            throw new \Exception('Number of parameters don\'t match uri params');
        }
        return str_replace($output[0], $params, $this->uri);
    }
    
    public function getSegment(int $index)
    {
        $segments = explode('/', $this->requestUrl ?? '');
        return $segments[$index] ?? null;
    }

    public function setController($controller)
    {
        $this->controller = trim($controller);
    }

    public function setAcceptedMethods($methods)
    {
        if (empty($methods)) {
            return;
        }
        switch(gettype($methods)) {
            case 'string':
                $this->acceptedMethods = explode(',', strtolower($methods));
                break;
            case 'array':
                $this->acceptedMethods = $methods;
                break;
        }
    }
    
    public static function createFromArray($route)
    {
        return new Route($route['id'], $route['path'], null, $route['@value'], $route['template'], $route);
    }
    
    public function setParameterValues(array $values = [])
    {        
        foreach(array_values($this->parameters) as $idx => $par) {
            $this->parameters[$par['id']]['value'] = $values[$idx] ?? null;
        }        
        $this->weight = count($values);
    }
    
    public function __get($key)
    {
        return array_key_exists($key, $this->route) ? $this->route[$key] : null;
    }

    public function __set($key, $value)
    {
        $this->route[$key] = $value;
    }

    public function __toString()
    {
        return $this->getUrl();
    }
    
    public function __invoke($key)
    {
        return $this->getParameter($key);
    }
    
    public function getTemplate()
    {        
        return request()->getTemplate($this->template);
    }
}
