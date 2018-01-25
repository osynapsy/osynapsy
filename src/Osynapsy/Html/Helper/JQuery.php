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
