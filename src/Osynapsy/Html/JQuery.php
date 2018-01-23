<?php
namespace Osynapsy\Html;

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
