<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Osynapsy\Core\Lib;

/**
 * Description of JQuery
 *
 * @author Pietro Celeste
 */
class JQuery
{
    private $elements = array();
    private $selector = '';
    
    public function __construct($selector)
    {
        $this->selector = $selector;
    }
    
    public function __call($method, $params)
    {
        $this->elements[$method] = $params;
        return $this;
    }
    
    public function __toString()
    {
        $string = '$(\''.$this->selector.'\')';
        foreach ($this->elements as $method => $params) {
            $string .= '.'.$method.'(';
            foreach ($params as $i => $par) {
                $string .= empty($i) ? '' : ',';
                $string .= is_string($par) ? '\''.addslashes($par).'\'' : $par;
            }
            $string .= ')';
        }
        return $string;
    }
}
