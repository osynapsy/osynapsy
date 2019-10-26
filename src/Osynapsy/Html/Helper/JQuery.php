<?php

/*
 * This file is part of the Osynapsy package.
 *
 * (c) Pietro Celeste <p.celeste@osynapsy.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Osynapsy\Html\Helper;

/**
 * Description of JQuery
 *
 * @author Pietro Celeste
 */
class JQuery
{
    private $elements = array();
    private $selector = '';
    private $response;
    
    public function __construct($selector, $response = null)
    {
        $this->selector = $selector;
        $this->response = $response;
    }
    
    public function __call($method, $params)
    {
        $this->elements[$method] = $params;
        return $this;
    }
    
    public function __toString()
    {
        $string = '$(\''.$this->selector.'\')';
        foreach ($this->elements as $method => $parameters) {
            $string .= $this->buildMethod($method, $parameters);
        }
        return $string;
    }
    
    private function buildMethod($method, $parameters)
    {
        if (empty($parameters)) {
            return ".{$method}())";        
        }
        $pars = implode("','", array_map(function($value){ return addslashes($value); }, $parameters));   
        return '.'.$method."('$pars')";
    }
    
    public function exec()
    {
        $this->response->js($this->__toString());
    }
}
