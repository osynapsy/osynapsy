<?php
namespace Osynapsy\Core\Helper\Database;

/**
 * Description of Sql
 *
 * @author Peter
 */
class Sql {
    //put your code here
    private $debug;
    private $part = [
        'select' => [' ', PHP_EOL],
        'from'   => [' ', PHP_EOL],
        'where'  => [' ', PHP_EOL],
        'ON' => [' (', ')'.PHP_EOL]
    ];
    private $elements = [0 => ['SELECT', array()] ];
    private $parameters = [];
    
    public function __construct($debug = false)
    {
        $this->debug = $debug;
    }
    
    public function condition($condition, $function)
    {
        if (!$condition) {
            return $this;
        }
        $function($this);
    }
    
    public function select($fields = null)
    {
        if (empty($fields)) {
            return;
        }
        if (!is_array($fields)) {
            $fields = array($fields);
        }
        $this->elements[0][1] = array_merge($this->elements[0][1], $fields);        
    }
    
    public function from($table, $fields = null)
    {
        $this->select($fields);
        $this->elements[] = ['FROM', $table];
        return $this;
    }
    
    public function join($table, array $on, array $fields = null)
    {
        $this->select($fields);
        $this->elements[] = ['INNER JOIN', $table];
        $this->elements[] = ['ON', $on];
        return $this;
    }
    
    public function joinLeft($table, array $on, array $fields = null)
    {
        $this->select($fields);
        $this->elements[] = ['LEFT JOIN', $table];
        $this->elements[] = ['ON', $on];
        return $this;
    }
    
    public function where($condition, array $parameters = array(), $operator = 'AND')
    {
        $this->elements[]['WHERE'][] = $condition;
        $this->parameters = array_merge(
            $this->parameters,
            is_array($parameters) ? $parameters : [$parameters]
        );
        return $this;
    }
    
    public function __call2($method, $params)
    {
        if (!is_array($params) || count($params) < 2) {
            $params = array($params, true);
        }
        $this->elements[$method] = $params;
        return $this;
    }
    
    public function __toString() {
        $string = '';
        foreach ($this->elements as $item) {
            $word = $item[0];
            $string .= $word.' ';
            $string .= $this->prefix($word);
            $string .= is_array($item[1]) ? implode(',', $item[1]) : $item[1];
            $string .= $this->postfix($word);
            if (array_key_exists($word, $this->part)) {
                $string .= $this->part[$word][1];
            }
            $string .= PHP_EOL;
        }
        return $string;
    }
    
    private function prefix($word)
    {
        return array_key_exists($word, $this->part) ? $this->part[$word][0] : ' ';
    }
    
    private function postfix($word)
    {
        return array_key_exists($word, $this->part) ? $this->part[$word][1] : ' ';
    }
}
