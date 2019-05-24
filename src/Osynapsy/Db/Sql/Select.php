<?php

/*
 * This file is part of the Osynapsy package.
 *
 * (c) Pietro Celeste <p.celeste@osynapsy.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Osynapsy\Db\Sql;

/**
 * Description of SqlQuery
 *
 * @author Peter
 */
class Select 
{
    private $debug;
    private $dummy;
    private $parent;    
    private $part = [
        'SELECT' => [
            'separator' => ','.PHP_EOL
        ],
        'JOIN' => [
            'separator' => ' '.PHP_EOL,
            'postfix' => PHP_EOL
        ],
        'WHERE' => [
            'separator' => ' '
        ],
        'ORDER BY' => [
            'separator' => ','.PHP_EOL
        ]
    ];
    private $elements = [
        'SELECT' => [],
        'FROM' => [],
        'JOIN' => [],
        'WHERE' => [],
        'ORDER BY' => []
    ];
    private $parameters = [];
    
    public function __construct($fields = [], $parent = null, $debug = false)
    {
        $this->debug = $debug;
        $this->parent = $parent;           
    }
    
    public function parameters(array $parameters = [])
    {
        $this->parameters = array_merge($this->parameters, $parameters);
        return $this;
    }
    
    private function isAssoc($array)
    {
        return !($array === array_values($array));
    }
    
    public function condition($condition, callable $function)
    {
        if (!$condition) {
            return $this;
        }
        $function($this);
        return $this;
    }
    
    public function select(array $fields = null, array $parameters = [])
    {
        if (empty($fields)) {
            return;
        }        
        if (!is_array($fields)) {
            $fields = array($fields);
        } elseif ($this->isAssoc($fields)) {
            $app = array();
            foreach($fields as $key => $value) {
                $app[] = $value.' as "'.$key.'"';
            }            
            $fields = $app;
        }
        $this->elements['SELECT'] = array_merge($this->elements['SELECT'], $fields);
        $this->parameters($parameters);
        return $this;
    }
    
    public function from($table, $fields = null)
    {
        $this->select($fields);
        $this->elements['FROM'] = $table;
        return $this;
    }
    
    public function join($table)
    {
        $this->elements['JOIN'][] = 'INNER JOIN '.$table;
        return $this;
    }
    
    public function joinLeft($table)
    {
        $this->elements['JOIN'][] = 'LEFT JOIN '.$table;
        return $this;
    }
    
    public function on(array $conditions)
    {
        $this->elements['JOIN'][] = 'ON ('.implode(' AND ', $conditions).')';
        return $this;
    }
    
    public function and_()
    {
        
    }
    
    public function where($rawCondition, array $parameters = [], $operator = 'AND')
    {
        $condition = is_array($rawCondition) ? '('.implode(' OR ', $rawCondition).')' 
                                             : $rawCondition;        
        if (!empty($this->elements['WHERE'])) {
            $condition = $operator . ' ' . $condition;
        } 
        $this->elements['WHERE'][] = $condition;
        $this->parameters($parameters);
        return $this;
    }
    
    public function orderBy(array $fields)
    {
        if (empty($fields)) {
            return;
        }
        $this->elements['ORDER BY'] = array_merge($this->elements['ORDER BY'], $fields);
        return $this;
    }
    
    public function __toString()
    {
        $string = '';
        foreach ($this->elements as $word => $items) {
            if (empty($items)) {
                continue;
            }
            $string .= $word == 'JOIN' ? '' : $word.' ';
            $string .= $this->prefix($word);            
            $string .= is_array($items) ? implode($this->getSeparator($word), $items) : ' '.$items;            
            $string .= $this->postfix($word);            
            $string .= PHP_EOL;
        }
        return $string;
    }
    
    public function getParameters()
    {
        return $this->parameters;
    }
                
    private function getSeparator($word)
    {
        return array_key_exists($word, $this->part) ? $this->part[$word]['separator'] : ' ';
    }
            
    private function prefix($word)
    {
        //return array_key_exists($word, $this->part) ? $this->part[$word][0] : ' ';
        return isset($this->part[$word]) && isset($this->part[$word]['prefix']) ? $this->part[$word]['prefix'] : ' ';
    }
    
    private function postfix($word)
    {
        return isset($this->part[$word]) && isset($this->part[$word]['postfix']) ? $this->part[$word]['postfix'] : ' ';
    }
    
    public function __if__($condition)
    {                
        return $condition ? $this : $this->getDummy(); 
    }
    
    public function __else__()
    {        
        return empty($this->parent) ? $this->getDummy() : $this->parent;
    }
    
    public function __endIf__()
    {
        return empty($this->parent) ? $this : $this->parent;
    }       
    
    private function getDummy()
    {
        if (empty($this->dummy)) {
            $this->dummy = new Select(null, $this);
        }
        return $this->dummy;
    }
}
