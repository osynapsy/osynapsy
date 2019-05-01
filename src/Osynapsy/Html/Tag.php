<?php

/*
 * This file is part of the Osynapsy package.
 *
 * (c) Pietro Celeste <p.celeste@osynapsy.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Osynapsy\Html;

class Tag
{
    //Attribute repo
    private $attributes = [];
    //Content repo
    private $childs = [];
    
    public $ref = array(); 
    public $tagdep = 0;
    public $parent = null;
    
    public function __construct($tag = 'dummy', $id = null, $class = null)
    {
        $this->att(0,$tag);
        if (!empty($id)) {
            $this->att('id', $id);
        }
        if (!empty($class)) {
            $this->att('class', $class);
        }
    }
    
    public function __get($attribute)
    {
        if ($attribute == 'tag') {
            return $this->attributes[0];
        }
        return array_key_exists($attribute, $this->attributes) ? $this->attributes[$attribute] : null;
    }
    
    public function __set($attribute, $value)
    {
       $this->attributes[$attribute] = $value;
    }
    
    public function add($child)
    {
        if ($child instanceof tag) {
            if ($child->id && array_key_exists($child->id,$this->ref)) {
                return $this->ref[$child->id];                
            }
            $child->tagdep = abs($this->tagdep) + 1;
            $this->tagdep = abs($this->tagdep) * -1;
        }
        //Append child to childs repo
        $this->childs[] = $child;
        //If child isn't object return $this tag
        if (!is_object($child)) {
            return $this;
        }
        if ($child->id) {
            $this->ref[$child->id] =& $child;
        }
        $child->parent =& $this;
        return $child;
    }
    
    public function addFromArray(array $array)
    {
        foreach ($array as $child) {
            $this->add($child);
        }
        return $child;
    }
    
    public function att($attribute, $value = '', $concat = false)
    {
        if (is_array($attribute)) {
            foreach ($attribute as $key => $value) {
                $this->attributes[$key] = $value;
            }            
        } elseif ($concat && !empty($this->attributes[$attribute])) {            
            $this->attributes[$attribute] .= ($concat === true ? ' ' : $concat) . $value;
        } else {
            $this->attributes[$attribute] = $value;
        }
        return $this;
    }
    
    protected function build()
    {
        $strContent = '';
        foreach ($this->childs as $content) {
            $strContent .= $content;
        }
        $tag = array_shift($this->attributes);
        if ($tag == 'dummy') {
            return $strContent;
        }
        $spaces = $strTag = '';
        if (!empty($tag)){
            $spaces = $this->tagdep != 0 ? "\n".str_repeat("  ",abs($this->tagdep)) : '';
            $strTag = $spaces.'<'.$tag;
            foreach ($this->attributes as $key => $val) {
                $strTag .= ' '.$key.'="'.htmlspecialchars($val, ENT_QUOTES).'"';
                // la conversione del contentuto degli attributi viene fornita da Tag in modo
                // tale che non debba essere gestito dai suoi figli
                /*$strTag .= ' '.$key.'="'.$val.'"';*/
            }
            $strTag .= '>';
        }
        if (!in_array($tag, array('input', 'img', 'link', 'meta'))) {
            $spaces2 = $this->tagdep < 0 ? $spaces : '';
            $strTag .= $strContent . (!empty($tag) ? $spaces2."</{$tag}>" : '');
        }
        return $strTag;
    }
    
    public static function create($tag, $id = null)
    {
        return new tag($tag,$id);
    }
    
    public function get()
    {
        return $this->build();
    }
    
    public function child($index = 0)
    {
        if (is_null($index)) {
            return $this->childs;   
        }
        if (array_key_exists($index, $this->childs)) {
            return $this->childs[$index];
        }
        return false;
    }
    
    public function isEmpty()
    {
        return count($this->childs) > 0 ? false : true;
    }

    public function __toString()
    {
        try {
            return $this->get();
        } catch (\Exception $e) {
            //var_dump($str);
            trigger_error($e->getMessage());
            echo '<pre>';
            var_dump(debug_backtrace(10));
            echo '</pre>';
            return $this->id;
        }
    }
}
